<?php
/**
 * Created by PhpStorm.
 * User: Personal
 * Date: 20/10/2017
 * Time: 5:17 PM
 */

namespace App\Models;


class ZoomAPI {

    //The API Key, Secret, & URL will be used in every function. LOCAL
    //private $api_key = '-a1qEaxUSQS1PQSwx7n_pg';
    //private $api_secret = 'OVuzQ0iWCCvLDTqIbRMJaYevcP5cr7x6KKsN';
    //private $api_url = 'https://api.zoom.us/v1/';

    /*The API Key, Secret, & URL will be used in every function. PRODUCTION*/
    private $api_key = 'TxSW6xwyQOyT-Fi1FvYn_A';
    private $api_secret = 'POL8wyzvlGUqt4N1phR7lzux7YwdLNoTzL7c';
    private $api_url = 'https://api.zoom.us/v1/';

    private function sendRequest($calledFunction, $data){

        $request_url = $this->api_url.$calledFunction;
        $data['api_key'] = $this->api_key;
        $data['api_secret'] = $this->api_secret;
        $data['data_type'] = 'JSON';

        $postFields = http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if(!$response){
            return false;
        }
        /*Return the data in JSON format*/
        return json_decode($response);
    }

    //users function
    public function createAUser($email){
        $createAUserArray = array();
        $createAUserArray['email'] = $email;
        $createAUserArray['type'] = 1;
        return $this->sendRequest('user/create', $createAUserArray);
    }

    public function getUserInfoByEmail($email){
        $getUserInfoByEmailArray = array();
        $getUserInfoByEmailArray['email'] = $email;
        return $this->sendRequest('user/getbyemail',$getUserInfoByEmailArray);
    }

    public function createAMeeting($teacher,$type,$start_date,$user,$duration=false){
        $createAMeetingArray = array();
        $createAMeetingArray['host_id'] = $teacher->zoom_id;
        $createAMeetingArray['topic'] = $type=="dele"?"BaseLang DELE Class":"BaseLang Real World Class With ".$user->first_name." ".$user->last_name;
        $createAMeetingArray['type'] = 2;
        $createAMeetingArray['start_time'] = $start_date->format('Y-m-d\TH:i:s\Z');//ISO
        $createAMeetingArray['duration'] = $duration?$duration:($type=="dele"?60:30);
        $createAMeetingArray['timezone'] = $teacher->timezone;
        $createAMeetingArray['option_jbh'] = true;
        return $this->sendRequest('meeting/create', $createAMeetingArray);
    }

    public function deleteAMeeting($meet_id,$teacher){
        $deleteAMeetingArray = array();
        $deleteAMeetingArray['id'] = $meet_id;
        $deleteAMeetingArray['host_id'] = $teacher->zoom_id;
        return $this->sendRequest('meeting/delete', $deleteAMeetingArray);
    }


    function listMeetings(){
        $listMeetingsArray = array();
        $listMeetingsArray['host_id'] = $_POST['userId'];
        return $this->sendRequest('meeting/list',$listMeetingsArray);
    }

    function getMeetingInfo($meet_id,$user_id){
        $getMeetingInfoArray = array();
        $getMeetingInfoArray['id'] = $meet_id;
        $getMeetingInfoArray['host_id'] = $user_id;
        return $this->sendRequest('meeting/get', $getMeetingInfoArray);
    }

    function updateMeetingInfo($meet_id,$teacher,$start_date,$duration){
        $updateMeetingInfoArray = array();
        $updateMeetingInfoArray['id'] = $meet_id;
        $updateMeetingInfoArray['host_id'] = $teacher->zoom_id;
        $updateMeetingInfoArray['start_time'] = $start_date->format('Y-m-d\TH:i:s\Z');
        $createAMeetingArray['timezone'] = $teacher->timezone;
        $updateMeetingInfoArray['duration'] = $duration;
        return $this->sendRequest('meeting/update', $updateMeetingInfoArray);
    }

    function endAMeeting(){
        $endAMeetingArray = array();
        $endAMeetingArray['id'] = $_POST['meetingId'];
        $endAMeetingArray['host_id'] = $_POST['userId'];
        return $this->sendRequest('meeting/end', $endAMeetingArray);
    }

    function listRecording(){
        $listRecordingArray = array();
        $listRecordingArray['host_id'] = $_POST['userId'];
        return $this->sendRequest('recording/list', $listRecordingArray);
    }

    /*Functions for management of users
    function createAUser(){...}
    function autoCreateAUser(){...}
    function custCreateAUser(){...}
    function deleteAUser(){...}
    function listUsers(){...}
    function listPendingUsers(){...}
    function getUserInfo(){...}
    function getUserInfoByEmail(){...}
    function updateUserInfo(){...}
    function updateUserPassword(){...}
    function setUserAssistant(){...}
    function deleteUserAssistant(){...}
    function revokeSSOToken(){...}
    function deleteUserPermanently(){...}

    /*Functions for management of meetings
    function createAMeeting(){...}
    function deleteAMeeting(){...}
    function listMeetings(){...}
    function getMeetingInfo(){...}
    function updateMeetingInfo(){...}
    function endAMeeting(){...}
    function listRecording(){...}

    /*Functions for management of reports
    function getDailyReport(){...}
    function getAccountReport(){...}
    function getUserReport(){...}

    /*Functions for management of webinars
    function createAWebinar(){...}
    function deleteAWebinar(){...}
    function listWebinars(){...}
    function getWebinarInfo(){...}
    function updateWebinarInfo(){...}
    function endAWebinar(){...}
    */
}
