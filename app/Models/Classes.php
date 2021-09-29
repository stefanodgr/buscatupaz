<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Classes extends Model
{

    protected $table = 'classes';

    protected $fillable = [
        'user_id',
        'class_time',
        'teacher_id',
        'type',
        'google_event_id',
        'zoom_id',
        'zoom_invitation',
        'location_id'
    ];

    public function getClassDateTime(){
        return \Datetime::createFromFormat("Y-m-d H:i:s",$this->class_time);
    }

    public function teacher()
    {
        return $this->hasOne('App\User','id','teacher_id');
    }

    public function student()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function location()
    {
        return $this->hasOne('App\Models\Location','id','location_id');
    }

    public static function fixTime($dateTime){
        if(!$dateTime) {
            return $dateTime;
        }
        try {
            $currentZone = clone $dateTime->getTimezone();
            $dateTime->setTimezone(new \DateTimeZone("UTC"));
            if($dateTime->format("i")!="00" && $dateTime->format("i")!="30"){
                Log::error('Error Time Fixed:'. var_export($dateTime,true));
                $i = intval($dateTime->format("i"));
                $compare = ["30"=>abs(30-$i),"60"=>abs(60-$i),"00"=>abs(00-$i)];
                $i = array_search(min($compare), $compare);
                $i = "60"?"00":$i;
                $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s",$dateTime->format("Y-m-d H:").$i.$dateTime->format(':s'))->setTimezone($currentZone);
            }
            $dateTime->setTimezone($currentZone);
        } catch (\Exception $e){
            Log::error('Error Fixing Time:'. var_export($dateTime,true));
        }

        return $dateTime;
    }

    public function removeZoom(){
        $subscriptionType=$this->type;
        $zoomEvent = new \stdClass();

        $classTime=\Datetime::createFromFormat("Y-m-d H:i:s",$this->class_time);
        $lowerTime=(clone $classTime)->sub(new \DateInterval('PT30M'));
        $upperTime=(clone $classTime)->add(new \DateInterval('PT30M'));

        if($subscriptionType=="dele"){
            $lowerTime->sub(new \DateInterval('PT30M'));
            $upperTime->add(new \DateInterval('PT30M'));
        }

        $lowerClass=Classes::where("zoom_id",$this->zoom_id)->where("class_time",$lowerTime->format("Y-m-d H:i:s"))->where("type",$this->type)->first();
        $upperClass=Classes::where("zoom_id",$this->zoom_id)->where("class_time",$upperTime->format("Y-m-d H:i:s"))->where("type",$this->type)->first();

        if($upperClass && $lowerClass){
            try {
                $zoomEvent->id = gmdate("U");
                Classes::where("zoom_id",$this->zoom_id)->where("class_time","<=",$lowerTime->format("Y-m-d H:i:s"))->update(["zoom_id"=>$zoomEvent->id]);
            } catch (\Exception $e) {
                Log::info(var_export($e->getMessage(),true));
            }
        }

        $this->removeGoogle();

        return true;
    }

    public function createZoom($teacher){
        $zoomEvent = new \stdClass();
        $subscriptionType=$this->type;
        //if($teacher->hasZoom()){
            //GET IN UTC
            $classTime=\Datetime::createFromFormat("Y-m-d H:i:s",$this->class_time);
            $lowerTime=(clone $classTime)->sub(new \DateInterval('PT30M'));
            $upperTime=(clone $classTime)->add(new \DateInterval('PT30M'));
            if($subscriptionType=="dele"){
                $lowerTime->sub(new \DateInterval('PT30M'));
                $upperTime->add(new \DateInterval('PT30M'));
            }

            $lowerClass=Classes::where("teacher_id",$teacher->id)->where("type",$this->type)->where("user_id",$this->user_id)->where("class_time",$lowerTime->format("Y-m-d H:i:s"))->whereNotNull("zoom_id")->first();
            $upperClass=Classes::where("teacher_id",$teacher->id)->where("type",$this->type)->where("user_id",$this->user_id)->where("class_time",$upperTime->format("Y-m-d H:i:s"))->whereNotNull("zoom_id")->first();

            if($upperClass && $lowerClass){
                try {
                    $upperClass=Classes::where("zoom_id",$upperClass->zoom_id)->orderBy("class_time","desc")->first();
                    $lowerClass=Classes::where("zoom_id",$lowerClass->zoom_id)->orderBy("class_time","asc")->first();


                    $zoomEvent->id=$lowerClass->zoom_id;

                    Classes::where("zoom_id",$upperClass->zoom_id)->update(["zoom_id"=>$zoomEvent->id]);
                    Classes::where("zoom_id",$lowerClass->zoom_id)->update(["zoom_id"=>$zoomEvent->id]);

                    $this->zoom_id=$zoomEvent->id;
                    $this->save();

                } catch (\Exception $e) {
                    Log::info(var_export($e->getMessage(),true));
                    $this->delete();
                    return false;
                }

            } elseif($upperClass) {
                try {
                    $upperClass=Classes::where("zoom_id",$upperClass->zoom_id)->orderBy("class_time","desc")->first();
                    $zoomEvent->id=$upperClass->zoom_id;

                    Classes::where("zoom_id",$upperClass->zoom_id)->update(["zoom_id"=>$zoomEvent->id]);

                    $this->zoom_id=$zoomEvent->id;
                    $this->save();

                } catch (\Exception $e) {
                    Log::info(var_export($e->getMessage(),true));
                    $this->delete();
                    return false;
                }

            } elseif($lowerClass) {
                try {
                    $lowerClass=Classes::where("zoom_id",$lowerClass->zoom_id)->orderBy("class_time","asc")->first();
                    $zoomEvent->id=$lowerClass->zoom_id;
                    Classes::where("zoom_id",$lowerClass->zoom_id)->update(["zoom_id"=>$zoomEvent->id]);

                    $this->zoom_id=$zoomEvent->id;
                    $this->save();

                } catch (\Exception $e) {
                    Log::info(var_export($e->getMessage(),true));
                    $this->delete();
                    return false;
                }

            } else {
                //new event
                try {
                    $zoomEvent->id=gmdate("U");
                    $this->zoom_id=$zoomEvent->id;
                    $this->save();
                } catch (\Exception $e){
                    Log::info(var_export($e->getMessage(),true));
                    $this->delete();
                    return false;
                }

            }

            $this->createGoogleCalendar($teacher);
            return true;
        //} else {
            //$this->delete();
            //return false;
        //}


    }

    public function removeGoogle(){
        $user = User::getCurrent();
        if(!$user){
            $user = $this->student;
        }

        if(isset($this->google_event_id) && $this->google_event_id && $user->getGoogleToken()){

            $googleClient = GoogleClient::getGoogleClient($this->student);
            $googleClient->setAccessToken($user->getGoogleToken());
            $service = new \Google_Service_Calendar($googleClient);

            $subscriptionType=$this->type;

            $classTime=\Datetime::createFromFormat("Y-m-d H:i:s",$this->class_time);
            $lowerTime=(clone $classTime)->sub(new \DateInterval('PT30M'));
            $upperTime=(clone $classTime)->add(new \DateInterval('PT30M'));

            if($subscriptionType=="dele"){
                $lowerTime->sub(new \DateInterval('PT30M'));
                $upperTime->add(new \DateInterval('PT30M'));
            }

            $lowerClass=Classes::where("google_event_id",$this->google_event_id)->where("class_time",$lowerTime->format("Y-m-d H:i:s"))->where("type",$this->type)->first();
            $upperClass=Classes::where("google_event_id",$this->google_event_id)->where("class_time",$upperTime->format("Y-m-d H:i:s"))->where("type",$this->type)->first();

            try {
                if($upperClass && $lowerClass){
                    $upperClass=Classes::where("google_event_id",$upperClass->google_event_id)->orderBy("class_time","desc")->first();
                    $endDate=\Datetime::createFromFormat("Y-m-d H:i:s",$upperClass->class_time);
                    $endDate->add(new \Dateinterval('PT30M'));
                    if($this->type=="dele"){
                        $endDate->add(new \Dateinterval('PT30M'));
                    }
                    $startDate=$upperTime;
                    $event = new \Google_Service_Calendar_Event(array(
                        'summary' => 'BaseLang Class',
                        'location' => 'Zoom',
                        'description' => 'BaseLang Class with '.$this->teacher->first_name.'.',
                        'start' => array(
                            'dateTime' => $startDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $endDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 15),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    ));

                    $service->events->update('primary', $this->google_event_id, $event);

                    //Se actualizo el upper, se debe crear el lower

                    $lowerClass=Classes::where("google_event_id",$lowerClass->google_event_id)->orderBy("class_time","asc")->first();
                    $startDate=\Datetime::createFromFormat("Y-m-d H:i:s",$lowerClass->class_time);;
                    $endDate=$classTime;
                    $event = new \Google_Service_Calendar_Event(array(
                        'summary' => 'BaseLang Class',
                        'location' => 'Zoom',
                        'description' => 'BaseLang Class with '.$this->teacher->first_name.'.',
                        'start' => array(
                            'dateTime' => $startDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $endDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 15),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    ));

                    $event = $service->events->insert('primary', $event);
                    Classes::where("class_time","<=",$lowerTime->format("Y-m-d H:i:s"))->where("google_event_id",$lowerClass->google_event_id)->update(["google_event_id"=>$event->id]);

                } elseif($upperClass) {
                    $upperClass=Classes::where("google_event_id",$upperClass->google_event_id)->orderBy("class_time","desc")->first();
                    $endDate=\Datetime::createFromFormat("Y-m-d H:i:s",$upperClass->class_time);
                    $endDate->add(new \Dateinterval('PT30M'));
                    if($this->type=="dele"){
                        $endDate->add(new \Dateinterval('PT30M'));
                    }
                    $startDate=$upperTime;
                    $event = new \Google_Service_Calendar_Event(array(
                        'summary' => 'BaseLang Class',
                        'location' => 'Zoom',
                        'description' => 'BaseLang Class with '.$this->teacher->first_name.'.',
                        'start' => array(
                            'dateTime' => $startDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $endDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 15),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    ));

                    $service->events->update('primary', $this->google_event_id, $event);
                } elseif($lowerClass) {
                    $lowerClass=Classes::where("google_event_id",$lowerClass->google_event_id)->orderBy("class_time","asc")->first();
                    $startDate=\Datetime::createFromFormat("Y-m-d H:i:s",$lowerClass->class_time);;
                    $endDate=$classTime;
                    $event = new \Google_Service_Calendar_Event(array(
                        'summary' => 'BaseLang Class',
                        'location' => 'Zoom',
                        'description' => 'BaseLang Class with '.$this->teacher->first_name.'.',
                        'start' => array(
                            'dateTime' => $startDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $endDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 15),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    ));

                    $service->events->update('primary', $this->google_event_id, $event);

                } else {
                    $service->events->delete('primary', $this->google_event_id);
                }
            } catch (\Exception $error) {
                Log::error("Error removing google: ".$error->getMessage());
                Log::info('User Credentials: '.$user->email.' '.var_export($user->google_token,true));
                return false;
            }
        }
        return true;
    }

    public function createGoogleCalendar($teacher){

        $user = User::getCurrent();
        if(!$user){
            $user = $this->student;
            if(!$user || !$user->id) {
                return false;
            }

        }
        $subscriptionType=session("current_subscription");
        if($user->getGoogleToken()){
            try {
                $googleClient=GoogleClient::getGoogleClient($this->student);
                $googleClient->setAccessToken($user->getGoogleToken());
                $service = new \Google_Service_Calendar($googleClient);
            } catch (\Exception $e) {
                Log::info("Problem With Google Token:".var_export($e->getMessage(),true));
                return false;
            }


            //GET IN UTC
            $classTime=\Datetime::createFromFormat("Y-m-d H:i:s",$this->class_time);
            $lowerTime=(clone $classTime)->sub(new \DateInterval('PT30M'));
            $upperTime=(clone $classTime)->add(new \DateInterval('PT30M'));
            if($subscriptionType=="dele"){
                $lowerTime->sub(new \DateInterval('PT30M'));
                $upperTime->add(new \DateInterval('PT30M'));
            }

            $lowerClass=Classes::where("teacher_id",$teacher->id)->where("type",$this->type)->where("user_id",$this->user_id)->where("class_time",$lowerTime->format("Y-m-d H:i:s"))->whereNotNull("google_event_id")->first();
            $upperClass=Classes::where("teacher_id",$teacher->id)->where("type",$this->type)->where("user_id",$this->user_id)->where("class_time",$upperTime->format("Y-m-d H:i:s"))->whereNotNull("google_event_id")->first();

            if($upperClass && $lowerClass){
                try {

                    //update lower level end to uper level end date
                    $lowerEvent = $service->events->get('primary', $lowerClass->google_event_id);
                    $upperEvent = $service->events->get('primary', $upperClass->google_event_id);
                    $lowerEvent->setLocation('Zoom');
                    $lowerEvent->setEnd($upperEvent->getEnd());
                    $service->events->update('primary', $lowerClass->google_event_id, $lowerEvent);

                    Classes::where('google_event_id',$upperClass->google_event_id)->update(["google_event_id"=>$lowerClass->google_event_id]);
                    $service->events->delete('primary', $upperClass->google_event_id);


                    $this->google_event_id=$lowerClass->google_event_id;
                    $this->save();

                    Log::info('Update Google Event: '. var_export($this->google_event_id,true));
                } catch (\Exception $e) {
                    Log::info('User Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    Log::info('Error Updating Google Calendar Up AND Lw: '.var_export($e->getMessage(),true));
                    return false;
                }

            } elseif($upperClass) {
                try {
                    $upperEvent = $service->events->get('primary', $upperClass->google_event_id);

                    $newDate = $upperEvent->getStart();
                    $startDate = \Datetime::createFromFormat('Y-m-d H:i:s', $this->class_time);
                    $newDate->dateTime = $startDate->format('Y-m-d\TH:i:sP');
                    $newDate->timeZone = $user->timezone;

                    $upperEvent->setStart($newDate);
                    $upperEvent->setLocation('Zoom');
                    $service->events->update('primary', $upperClass->google_event_id, $upperEvent);

                    $this->google_event_id=$upperClass->google_event_id;
                    $this->save();
                    Log::info('Update Google Event: '. var_export($this->google_event_id,true));
                } catch (\Exception $e) {
                    Log::info('User Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    Log::info('Error Updating Google Calendar Up: '.var_export($e->getMessage(),true));
                    return false;
                }

            } elseif($lowerClass) {
                try {
                    $lowerEvent = $service->events->get('primary', $lowerClass->google_event_id);
                    $newDate = $lowerEvent->getEnd();
                    $endDate = \Datetime::createFromFormat('Y-m-d H:i:s', $this->class_time);
                    $endDate->add(new \Dateinterval('PT30M'));
                    if($this->type=="dele"){
                        //DELE 1 hour to join
                        $endDate->add(new \Dateinterval('PT30M'));
                    }

                    $newDate->dateTime = $endDate->format('Y-m-d\TH:i:sP');
                    $newDate->timeZone = $user->timezone;

                    $lowerEvent->setEnd($newDate);
                    $lowerEvent->setLocation('Zoom');
                    $service->events->update('primary', $lowerClass->google_event_id, $lowerEvent);

                    $this->google_event_id=$lowerClass->google_event_id;
                    $this->save();
                    Log::info('Update Google Event: '. var_export($this->google_event_id,true));
                } catch (\Exception $e) {
                    Log::info('User Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    Log::info('Error Updating Google Calendar Lw: '.var_export($e->getMessage(),true));
                    return false;
                }

            } else {
                //new event
                try {
                    $startDate=\Datetime::createFromFormat('Y-m-d H:i:s',$this->class_time);

                    $endDate = \Datetime::createFromFormat('Y-m-d H:i:s', $this->class_time);
                    $endDate->add(new \Dateinterval('PT30M'));
                    if($this->type=="dele"){
                        //DELE 1 hour to join
                        $endDate->add(new \Dateinterval('PT30M'));
                    }

                    $event = new \Google_Service_Calendar_Event(array(
                        'summary' => 'BaseLang Class',
                        'location' => 'Zoom',
                        'description' => 'BaseLang Class with '.$teacher->first_name.'.',
                        'start' => array(
                            'dateTime' => $startDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $endDate->format('Y-m-d\TH:i:sP'),
                            'timeZone' => $user->timezone,
                        ),
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 15),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    ));


                    $event = $service->events->insert('primary', $event);
                    $this->google_event_id=$event->id;
                    $this->save();
                    Log::info('Create Google Event: '. var_export($this->google_event_id,true));
                } catch (\Exception $e){
                    Log::info('User Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    Log::info('Error Creating Google Calendar: '.var_export($e->getMessage(),true));
                    return false;
                }

            }
            return true;
        } else {
            return false;
        }
    }

}