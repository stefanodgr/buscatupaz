<?php

namespace Modules\Classes\Entities;

use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
//use Modules\Course\Entities\Course;
//use Modules\Platform\Entities\Platform;
use Modules\User\Entities\Permission;
use Modules\User\Entities\User;

class Zoom
{
    protected $api_key = 'VRvdk3rTQh-ST4gQ6D49qA';
    protected $api_secret = 'G7PEah1xP7VRSaWYBtuNmQJmaJXqhya4a3As';
    protected $api_url = 'https://api.zoom.us/v2/';
    //protected $user_email ='info@tooeasyenglish.com';

    public function __construct(){
        $this->loadKeys();
    }

    public function loadKeys(){
        $platform = "Buscatupaz.com";
        $keys = ['api_key','api_secret','api_url'];

        foreach($keys as $key){
            if(isset($platform->zoom[$key]) && $platform->zoom[$key]){
                $this->$key = $platform->zoom[$key];
            }
        }
    }

    private static function sendRequest($calledFunction, $data,$method='GET'){

        $zoom = new Zoom();

        $request_url = $zoom->api_url.$calledFunction;
        $jwt=JWT::encode(["iss"=>$zoom->api_key,"exp"=>gmdate('U',strtotime("10 seconds")),"iat"=>gmdate('U')],$zoom->api_secret);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_URL,$request_url);
        curl_setopt($curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$method);
        curl_setopt($curl,CURLOPT_HTTPHEADER,["authorization: Bearer ".$jwt,"content-type: application/json"]);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        if($method=="POST" || $method=="PATCH"){
            curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data));
        }
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl)['http_code'];
        curl_close($curl);

        //Log::info('Zoom response: '.var_export($response,true));
        if ($err || ($code!=201 && $code!=200 && $code!=204)) {
            Log::error('Error on zoom: '.var_export($err,true));
            Log::error($code);
            Log::error(var_export($response,true));
            return false;
        }

        return json_decode($response);
    }

    public static function deleteAMeeting($class){
        return self::sendRequest('meetings/'.$class->zoom_id, [],"DELETE");
    }

    public static function getAMeeting($class){
        return self::sendRequest('meetings/'.$class->zoom_id, []);
    }


  

    public static function createAMeeting($class){
        $meeting = [];
        //$platform = Platform::getCurrentPlatform();

        $meeting['topic'] = "Buscatupaz.com : Sesión ".$class->type;
        $meeting['type'] = 2;
        //$meeting['start_time'] = $class->datetime->format('Y-m-d\TH:i:s\Z');
        $meeting['start_time'] = $class->class_time;
        
        $meeting['duration'] = 30;
        //$meeting['timezone'] = $class->teacher->timezone;
        $meeting['password'] = '';
        $meeting['settings'] = [];
        $meeting['settings']['waiting_room'] = false;
        $meeting['settings']['join_before_host'] = true;
        return self::sendRequest('users/'.$class->teacher->email.'/meetings', $meeting,"POST");
    }

    public static function updateAMeeting($class){
        $currrent_meeting = Zoom::getAMeeting($class);
        $meeting = [];
        $agenda = [];
        Log::info('Current Meeting: '.var_export($currrent_meeting,true));

        try {
            if(isset($currrent_meeting) && isset($currrent_meeting->agenda)){
                $agenda = json_decode($currrent_meeting->agenda,true);
                if(!$agenda){
                    $agenda = [];
                }
            } else {
                $agenda = [];
            }
        } catch (\Exception $e){
            $agenda = [];
        }


        if(!in_array($class->user->identification,$agenda)){
            $agenda[]=$class->user->identification;
        }

        $meeting['agenda'] = json_encode($agenda);

        return self::sendRequest('meetings/'.$class->zoom_id, $meeting,"PATCH");
    }

}
