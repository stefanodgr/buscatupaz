<?php

namespace App;

use App\Models\ActiveLocation;
use App\Models\BuyInmersion;
use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\GoogleClient;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Location;
use App\Models\Role;
use App\Models\Plan;
use App\Models\ScheduledChanges;
use App\Models\Statistics;
use App\Models\Subscription;
use App\Models\UserCalendar;
use App\Models\UserCancellation;
use App\Models\UserCredits;
use App\Models\UserEvaluation;
use App\Models\UserFreeDays;
use App\Models\ZoomAPI;
use App\Models\Error;
use Chargebee\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class User extends Authenticatable
{
    use EntrustUserTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'id_number',
        'mobile_number',
        'email',
        'activated',
        'password',
        'description',
        'notes',
        'timezone',
        'options',
        'zoom_id',
        'last_login',
        'chargebee_id',
        'credits',
        'paypal_email',
        'card_last_four',
        'payment_method_token',
        'referral_email',
        'is_deleteacher',
        'youtube_url',
        'location',
        'evaluation',
        'gender',
        'teaching_style',
        'strongest_with',
        'english_level',
        'favorite_teacher',
        'favorite_teacher_time',
        'google_token',
        'refresh_google_token',
        'user_level',
        'pay_image',
        'last_unlimited_subscription',
        'electives_sheet',
        'dele_sheet',
        'real_sheet',
        'location_id',
        'registered_inmersion',
        'read_prebook',
        'block_online',
        'referral_code',
        'block_prebook',
        'check_landing_date',
        'location_half',
        'company'
    ];

    protected $dates = [
        'favorite_teacher_time','created_at','updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function refreshInformation(){
        $user = User::getCurrent();
        $this->clearCache();
        if($user && $this->id==$user->id){
            session(['subscription_check'=>gmdate('Y-m-d H:i:s')]);
            $this->last_login=date('Y-m-d H:i:s');
            $this->secureSave();
        }

        $this->verifyRole();
        $this->updateSubscriptionInfo();
        $this->checkCredits();
        return true;
    }

    public function clearCache(){
        unset($this->cache_immersion);
        unset($this->cache_has_immersion);
        unset($this->cache_calendar);
        unset($this->cache_current_rol);
        unset($this->cache_subscription);
    }

    /**
     * @return bool|product
     */
    public function getImmersionAttribute(){
        if(!$this->has_immersion){
            return false;
        }

        if(!isset($this->cache_immersion)){
            $this->cache_immersion = $this->getImmersion();
        }

        return $this->cache_immersion;
    }

    /**
     * @return bool
     */
    public function getHasImmersionAttribute(){
        if(!isset($this->cache_has_immersion)){
            $this->cache_has_immersion = $this->hasImmersion();
        }
        if($this->cache_has_immersion){
            return true;
        }
        return false;
    }

    /**
     * Check if user has immersion starting "soon", can be called by a teacher
     * @bool $teacher
     * @return Product
     */
    public function getImmersion($teacher=false){
        if(!$teacher) {
            return $this->products()->where('product', 'immersion')->where('status', 1)->where('limit_date', '>', gmdate('Y-m-d'))->orderBy('activation_date', 'asc')->first();
        } else {
            return $this->productsTeacher()->where('product', 'immersion')->where('status', 1)->where('limit_date', '>', gmdate('Y-m-d'))->orderBy('activation_date', 'asc')->first();
        }
    }

    /**
     * Check if user has immersion starting "soon"
     * @return boolean
     */
    public function hasImmersion(){
        if($this->products()->where('product','immersion')->where('status',1)->where('limit_date','>',gmdate('Y-m-d'))->first()){
            return true;
        };
        return false;
    }

    public function getSavedCalendar($day){

        $fullCalendar=collect();

        foreach($this->getCalendar()->where("day",$day)->get() as $calendar){
            $utc_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day);
            $user_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day)->setTimezone(new \DateTimeZone($this->timezone));
            if($user_zone->format("j")!=$utc_zone->format("j")){
                continue;
            }
            $fullCalendar->push($calendar);
        };


        $nextDay = ($day+1==8?1:$day+1);
        foreach($this->getCalendar()->where("day",$nextDay)->get() as $calendar){
            $utc_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day)->sub(new \DateInterval("P1D"));
            $user_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day)->setTimezone(new \DateTimeZone($this->timezone));

            if($user_zone->format("j")!=$utc_zone->format("j")){
                continue;
            }

            $fullCalendar->push($calendar);

        };




        $lastDay = ($day-1==0?7:$day-1);

        foreach($this->getCalendar()->where("day",$lastDay)->get() as $calendar){
            $utc_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day)->add(new \DateInterval("P1D"));
            $user_zone=\DateTime::createFromFormat("H:i:s j",$calendar->from." ".$calendar->day)->setTimezone(new \DateTimeZone($this->timezone));

            if($user_zone->format("j")!=$utc_zone->format("j")){
                continue;
            }

            $fullCalendar->push($calendar);
        };



        return $fullCalendar;
    }

    public function getProgressLevels($subscriptionType){
        $response = [0,0,false];
        $level_ids = array();
        $levels = Level::where("enabled",1)->where("type",$subscriptionType)->orderBy("level_order","asc")->get();
        foreach($levels as $level){
            $level_ids[] = $level->id;
        }

        $lesson_ids = array();
        $lessonsids_levelids_mapping = array();
        $level_lessons_mapping = array();
        $level_lessons = Lesson::whereIn("level_id",$level_ids)->where("enabled",1)->get();
        $user_lessons_mapping = array();
        foreach($level_lessons as $level_lesson) {
            $lesson_ids[] = $level_lesson->id;
            $lessonsids_levelids_mapping[$level_lesson->id] = $level_lesson->level_id;
            if(array_key_exists($level_lesson->level_id, $level_lessons_mapping)){
                $lesson_array = $level_lessons_mapping[$level_lesson->level_id]; 
                array_push($lesson_array, $level_lesson);
                $level_lessons_mapping[$level_lesson->level_id] = $lesson_array;
            } else{
                $lesson_array = array();
                array_push($lesson_array, $level_lesson);
                $level_lessons_mapping[$level_lesson->level_id] = $lesson_array;
            } 
        }

        $user_lessons_mapping = array();
        $user_lessons = $this->lessons()->whereIn("lesson_id",$lesson_ids)->where("completed",1)->get();
        foreach($user_lessons as $user_lesson) {
            $level_id = $lessonsids_levelids_mapping[$user_lesson->id];
            if(array_key_exists($level_id, $user_lessons_mapping)){
                $user_lesson_array = $user_lessons_mapping[$level_id]; 
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            } else{
                $user_lesson_array = array();
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            }
        }

        foreach($levels as &$level){
            $level->lessons=$level_lessons_mapping[$level->id];
            $level->lessons_total = sizeof($level->lessons);
            if(sizeof($user_lessons_mapping)>0 && array_key_exists($level->id, $user_lessons_mapping)) {
                $level->lessons_completed = sizeof($user_lessons_mapping[$level->id]);
            } else {
                $level->lessons_completed = 0;
            }
        };

        $response[0]=$level_lessons->count();
        $response[1]=$user_lessons->count();
        $response[2]=$levels;
        return $response;
    }

    public function getTimeZones(){
        $timeZones=["Africa"=>[["Africa/Abidjan","Abidjan"],["Africa/Accra","Accra"],["Africa/Addis_Ababa","Addis_Ababa"],["Africa/Algiers","Algiers"],["Africa/Asmara","Asmara"],["Africa/Bamako","Bamako"],["Africa/Bangui","Bangui"],["Africa/Banjul","Banjul"],["Africa/Bissau","Bissau"],["Africa/Blantyre","Blantyre"],["Africa/Brazzaville","Brazzaville"],["Africa/Bujumbura","Bujumbura"],["Africa/Cairo","Cairo"],["Africa/Casablanca","Casablanca"],["Africa/Ceuta","Ceuta"],["Africa/Conakry","Conakry"],["Africa/Dakar","Dakar"],["Africa/Dar_es_Salaam","Dar_es_Salaam"],["Africa/Djibouti","Djibouti"],["Africa/Douala","Douala"],["Africa/El_Aaiun","El_Aaiun"],["Africa/Freetown","Freetown"],["Africa/Gaborone","Gaborone"],["Africa/Harare","Harare"],["Africa/Johannesburg","Johannesburg"],["Africa/Juba","Juba"],["Africa/Kampala","Kampala"],["Africa/Khartoum","Khartoum"],["Africa/Kigali","Kigali"],["Africa/Kinshasa","Kinshasa"],["Africa/Lagos","Lagos"],["Africa/Libreville","Libreville"],["Africa/Lome","Lome"],["Africa/Luanda","Luanda"],["Africa/Lubumbashi","Lubumbashi"],["Africa/Lusaka","Lusaka"],["Africa/Malabo","Malabo"],["Africa/Maputo","Maputo"],["Africa/Maseru","Maseru"],["Africa/Mbabane","Mbabane"],["Africa/Mogadishu","Mogadishu"],["Africa/Monrovia","Monrovia"],["Africa/Nairobi","Nairobi"],["Africa/Ndjamena","Ndjamena"],["Africa/Niamey","Niamey"],["Africa/Nouakchott","Nouakchott"],["Africa/Ouagadougou","Ouagadougou"],["Africa/Porto-Novo","Porto-Novo"],["Africa/Sao_Tome","Sao_Tome"],["Africa/Tripoli","Tripoli"],["Africa/Tunis","Tunis"],["Africa/Windhoek","Windhoek"]],"America"=>[["America/Adak","Adak"],["America/Anchorage","Anchorage"],["America/Anguilla","Anguilla"],["America/Antigua","Antigua"],["America/Araguaina","Araguaina"],["America/Argentina/Buenos_Aires","Argentina/Buenos_Aires"],["America/Argentina/Catamarca","Argentina/Catamarca"],["America/Argentina/Cordoba","Argentina/Cordoba"],["America/Argentina/Jujuy","Argentina/Jujuy"],["America/Argentina/La_Rioja","Argentina/La_Rioja"],["America/Argentina/Mendoza","Argentina/Mendoza"],["America/Argentina/Rio_Gallegos","Argentina/Rio_Gallegos"],["America/Argentina/Salta","Argentina/Salta"],["America/Argentina/San_Juan","Argentina/San_Juan"],["America/Argentina/San_Luis","Argentina/San_Luis"],["America/Argentina/Tucuman","Argentina/Tucuman"],["America/Argentina/Ushuaia","Argentina/Ushuaia"],["America/Aruba","Aruba"],["America/Asuncion","Asuncion"],["America/Atikokan","Atikokan"],["America/Bahia","Bahia"],["America/Bahia_Banderas","Bahia_Banderas"],["America/Barbados","Barbados"],["America/Belem","Belem"],["America/Belize","Belize"],["America/Blanc-Sablon","Blanc-Sablon"],["America/Boa_Vista","Boa_Vista"],["America/Bogota","Bogota"],["America/Boise","Boise"],["America/Cambridge_Bay","Cambridge_Bay"],["America/Campo_Grande","Campo_Grande"],["America/Cancun","Cancun"],["America/Caracas","Caracas"],["America/Cayenne","Cayenne"],["America/Cayman","Cayman"],["America/Chicago","Chicago"],["America/Chihuahua","Chihuahua"],["America/Costa_Rica","Costa_Rica"],["America/Creston","Creston"],["America/Cuiaba","Cuiaba"],["America/Curacao","Curacao"],["America/Danmarkshavn","Danmarkshavn"],["America/Dawson","Dawson"],["America/Dawson_Creek","Dawson_Creek"],["America/Denver","Denver"],["America/Detroit","Detroit"],["America/Dominica","Dominica"],["America/Edmonton","Edmonton"],["America/Eirunepe","Eirunepe"],["America/El_Salvador","El_Salvador"],["America/Fort_Nelson","Fort_Nelson"],["America/Fortaleza","Fortaleza"],["America/Glace_Bay","Glace_Bay"],["America/Godthab","Godthab"],["America/Goose_Bay","Goose_Bay"],["America/Grand_Turk","Grand_Turk"],["America/Grenada","Grenada"],["America/Guadeloupe","Guadeloupe"],["America/Guatemala","Guatemala"],["America/Guayaquil","Guayaquil"],["America/Guyana","Guyana"],["America/Halifax","Halifax"],["America/Havana","Havana"],["America/Hermosillo","Hermosillo"],["America/Indiana/Indianapolis","Indiana/Indianapolis"],["America/Indiana/Knox","Indiana/Knox"],["America/Indiana/Marengo","Indiana/Marengo"],["America/Indiana/Petersburg","Indiana/Petersburg"],["America/Indiana/Tell_City","Indiana/Tell_City"],["America/Indiana/Vevay","Indiana/Vevay"],["America/Indiana/Vincennes","Indiana/Vincennes"],["America/Indiana/Winamac","Indiana/Winamac"],["America/Inuvik","Inuvik"],["America/Iqaluit","Iqaluit"],["America/Jamaica","Jamaica"],["America/Juneau","Juneau"],["America/Kentucky/Louisville","Kentucky/Louisville"],["America/Kentucky/Monticello","Kentucky/Monticello"],["America/Kralendijk","Kralendijk"],["America/La_Paz","La_Paz"],["America/Lima","Lima"],["America/Los_Angeles","Los_Angeles"],["America/Lower_Princes","Lower_Princes"],["America/Maceio","Maceio"],["America/Managua","Managua"],["America/Manaus","Manaus"],["America/Marigot","Marigot"],["America/Martinique","Martinique"],["America/Matamoros","Matamoros"],["America/Mazatlan","Mazatlan"],["America/Menominee","Menominee"],["America/Merida","Merida"],["America/Metlakatla","Metlakatla"],["America/Mexico_City","Mexico_City"],["America/Miquelon","Miquelon"],["America/Moncton","Moncton"],["America/Monterrey","Monterrey"],["America/Montevideo","Montevideo"],["America/Montserrat","Montserrat"],["America/Nassau","Nassau"],["America/New_York","New_York"],["America/Nipigon","Nipigon"],["America/Nome","Nome"],["America/Noronha","Noronha"],["America/North_Dakota/Beulah","North_Dakota/Beulah"],["America/North_Dakota/Center","North_Dakota/Center"],["America/North_Dakota/New_Salem","North_Dakota/New_Salem"],["America/Ojinaga","Ojinaga"],["America/Panama","Panama"],["America/Pangnirtung","Pangnirtung"],["America/Paramaribo","Paramaribo"],["America/Phoenix","Phoenix"],["America/Port-au-Prince","Port-au-Prince"],["America/Port_of_Spain","Port_of_Spain"],["America/Porto_Velho","Porto_Velho"],["America/Puerto_Rico","Puerto_Rico"],["America/Rainy_River","Rainy_River"],["America/Rankin_Inlet","Rankin_Inlet"],["America/Recife","Recife"],["America/Regina","Regina"],["America/Resolute","Resolute"],["America/Rio_Branco","Rio_Branco"],["America/Santarem","Santarem"],["America/Santiago","Santiago"],["America/Santo_Domingo","Santo_Domingo"],["America/Sao_Paulo","Sao_Paulo"],["America/Scoresbysund","Scoresbysund"],["America/Sitka","Sitka"],["America/St_Barthelemy","St_Barthelemy"],["America/St_Johns","St_Johns"],["America/St_Kitts","St_Kitts"],["America/St_Lucia","St_Lucia"],["America/St_Thomas","St_Thomas"],["America/St_Vincent","St_Vincent"],["America/Swift_Current","Swift_Current"],["America/Tegucigalpa","Tegucigalpa"],["America/Thule","Thule"],["America/Thunder_Bay","Thunder_Bay"],["America/Tijuana","Tijuana"],["America/Toronto","Toronto"],["America/Tortola","Tortola"],["America/Vancouver","Vancouver"],["America/Whitehorse","Whitehorse"],["America/Winnipeg","Winnipeg"],["America/Yakutat","Yakutat"],["America/Yellowknife","Yellowknife"]],"Antarctica"=>[["Antarctica/Casey","Casey"],["Antarctica/Davis","Davis"],["Antarctica/DumontDUrville","DumontDUrville"],["Antarctica/Macquarie","Macquarie"],["Antarctica/Mawson","Mawson"],["Antarctica/McMurdo","McMurdo"],["Antarctica/Palmer","Palmer"],["Antarctica/Rothera","Rothera"],["Antarctica/Syowa","Syowa"],["Antarctica/Troll","Troll"],["Antarctica/Vostok","Vostok"]],"Asia"=>[["Asia/Aden","Aden"],["Asia/Almaty","Almaty"],["Asia/Amman","Amman"],["Asia/Anadyr","Anadyr"],["Asia/Aqtau","Aqtau"],["Asia/Aqtobe","Aqtobe"],["Asia/Ashgabat","Ashgabat"],["Asia/Baghdad","Baghdad"],["Asia/Bahrain","Bahrain"],["Asia/Baku","Baku"],["Asia/Bangkok","Bangkok"],["Asia/Barnaul","Barnaul"],["Asia/Beirut","Beirut"],["Asia/Bishkek","Bishkek"],["Asia/Brunei","Brunei"],["Asia/Chita","Chita"],["Asia/Choibalsan","Choibalsan"],["Asia/Colombo","Colombo"],["Asia/Damascus","Damascus"],["Asia/Dhaka","Dhaka"],["Asia/Dili","Dili"],["Asia/Dubai","Dubai"],["Asia/Dushanbe","Dushanbe"],["Asia/Gaza","Gaza"],["Asia/Hebron","Hebron"],["Asia/Ho_Chi_Minh","Ho_Chi_Minh"],["Asia/Hong_Kong","Hong_Kong"],["Asia/Hovd","Hovd"],["Asia/Irkutsk","Irkutsk"],["Asia/Jakarta","Jakarta"],["Asia/Jayapura","Jayapura"],["Asia/Jerusalem","Jerusalem"],["Asia/Kabul","Kabul"],["Asia/Kamchatka","Kamchatka"],["Asia/Karachi","Karachi"],["Asia/Kathmandu","Kathmandu"],["Asia/Khandyga","Khandyga"],["Asia/Kolkata","Kolkata"],["Asia/Krasnoyarsk","Krasnoyarsk"],["Asia/Kuala_Lumpur","Kuala_Lumpur"],["Asia/Kuching","Kuching"],["Asia/Kuwait","Kuwait"],["Asia/Macau","Macau"],["Asia/Magadan","Magadan"],["Asia/Makassar","Makassar"],["Asia/Manila","Manila"],["Asia/Muscat","Muscat"],["Asia/Nicosia","Nicosia"],["Asia/Novokuznetsk","Novokuznetsk"],["Asia/Novosibirsk","Novosibirsk"],["Asia/Omsk","Omsk"],["Asia/Oral","Oral"],["Asia/Phnom_Penh","Phnom_Penh"],["Asia/Pontianak","Pontianak"],["Asia/Pyongyang","Pyongyang"],["Asia/Qatar","Qatar"],["Asia/Qyzylorda","Qyzylorda"],["Asia/Rangoon","Rangoon"],["Asia/Riyadh","Riyadh"],["Asia/Sakhalin","Sakhalin"],["Asia/Samarkand","Samarkand"],["Asia/Seoul","Seoul"],["Asia/Shanghai","Shanghai"],["Asia/Singapore","Singapore"],["Asia/Srednekolymsk","Srednekolymsk"],["Asia/Taipei","Taipei"],["Asia/Tashkent","Tashkent"],["Asia/Tbilisi","Tbilisi"],["Asia/Tehran","Tehran"],["Asia/Thimphu","Thimphu"],["Asia/Tokyo","Tokyo"],["Asia/Tomsk","Tomsk"],["Asia/Ulaanbaatar","Ulaanbaatar"],["Asia/Urumqi","Urumqi"],["Asia/Ust-Nera","Ust-Nera"],["Asia/Vientiane","Vientiane"],["Asia/Vladivostok","Vladivostok"],["Asia/Yakutsk","Yakutsk"],["Asia/Yekaterinburg","Yekaterinburg"],["Asia/Yerevan","Yerevan"]],"Atlantic"=>[["Atlantic/Azores","Azores"],["Atlantic/Bermuda","Bermuda"],["Atlantic/Canary","Canary"],["Atlantic/Cape_Verde","Cape_Verde"],["Atlantic/Faroe","Faroe"],["Atlantic/Madeira","Madeira"],["Atlantic/Reykjavik","Reykjavik"],["Atlantic/South_Georgia","South_Georgia"],["Atlantic/St_Helena","St_Helena"],["Atlantic/Stanley","Stanley"]],"Europe"=>[["Europe/Amsterdam","Amsterdam"],["Europe/Andorra","Andorra"],["Europe/Astrakhan","Astrakhan"],["Europe/Athens","Athens"],["Europe/Belgrade","Belgrade"],["Europe/Berlin","Berlin"],["Europe/Bratislava","Bratislava"],["Europe/Brussels","Brussels"],["Europe/Bucharest","Bucharest"],["Europe/Budapest","Budapest"],["Europe/Busingen","Busingen"],["Europe/Chisinau","Chisinau"],["Europe/Copenhagen","Copenhagen"],["Europe/Dublin","Dublin"],["Europe/Gibraltar","Gibraltar"],["Europe/Guernsey","Guernsey"],["Europe/Helsinki","Helsinki"],["Europe/Isle_of_Man","Isle_of_Man"],["Europe/Istanbul","Istanbul"],["Europe/Jersey","Jersey"],["Europe/Kaliningrad","Kaliningrad"],["Europe/Kiev","Kiev"],["Europe/Kirov","Kirov"],["Europe/Lisbon","Lisbon"],["Europe/Ljubljana","Ljubljana"],["Europe/London","London"],["Europe/Luxembourg","Luxembourg"],["Europe/Madrid","Madrid"],["Europe/Malta","Malta"],["Europe/Mariehamn","Mariehamn"],["Europe/Minsk","Minsk"],["Europe/Monaco","Monaco"],["Europe/Moscow","Moscow"],["Europe/Oslo","Oslo"],["Europe/Paris","Paris"],["Europe/Podgorica","Podgorica"],["Europe/Prague","Prague"],["Europe/Riga","Riga"],["Europe/Rome","Rome"],["Europe/Samara","Samara"],["Europe/San_Marino","San_Marino"],["Europe/Sarajevo","Sarajevo"],["Europe/Simferopol","Simferopol"],["Europe/Skopje","Skopje"],["Europe/Sofia","Sofia"],["Europe/Stockholm","Stockholm"],["Europe/Tallinn","Tallinn"],["Europe/Tirane","Tirane"],["Europe/Ulyanovsk","Ulyanovsk"],["Europe/Uzhgorod","Uzhgorod"],["Europe/Vaduz","Vaduz"],["Europe/Vatican","Vatican"],["Europe/Vienna","Vienna"],["Europe/Vilnius","Vilnius"],["Europe/Volgograd","Volgograd"],["Europe/Warsaw","Warsaw"],["Europe/Zagreb","Zagreb"],["Europe/Zaporozhye","Zaporozhye"],["Europe/Zurich","Zurich"]],"Indian"=>[["Indian/Antananarivo","Antananarivo"],["Indian/Chagos","Chagos"],["Indian/Christmas","Christmas"],["Indian/Cocos","Cocos"],["Indian/Comoro","Comoro"],["Indian/Kerguelen","Kerguelen"],["Indian/Mahe","Mahe"],["Indian/Maldives","Maldives"],["Indian/Mauritius","Mauritius"],["Indian/Mayotte","Mayotte"],["Indian/Reunion","Reunion"]],"Pacific"=>[["Pacific/Apia","Apia"],["Pacific/Auckland","Auckland"],["Pacific/Bougainville","Bougainville"],["Pacific/Chatham","Chatham"],["Pacific/Chuuk","Chuuk"],["Pacific/Easter","Easter"],["Pacific/Efate","Efate"],["Pacific/Enderbury","Enderbury"],["Pacific/Fakaofo","Fakaofo"],["Pacific/Fiji","Fiji"],["Pacific/Funafuti","Funafuti"],["Pacific/Galapagos","Galapagos"],["Pacific/Gambier","Gambier"],["Pacific/Guadalcanal","Guadalcanal"],["Pacific/Guam","Guam"],["Pacific/Honolulu","Honolulu"],["Pacific/Johnston","Johnston"],["Pacific/Kiritimati","Kiritimati"],["Pacific/Kosrae","Kosrae"],["Pacific/Kwajalein","Kwajalein"],["Pacific/Majuro","Majuro"],["Pacific/Marquesas","Marquesas"],["Pacific/Midway","Midway"],["Pacific/Nauru","Nauru"],["Pacific/Niue","Niue"],["Pacific/Norfolk","Norfolk"],["Pacific/Noumea","Noumea"],["Pacific/Pago_Pago","Pago_Pago"],["Pacific/Palau","Palau"],["Pacific/Pitcairn","Pitcairn"],["Pacific/Pohnpei","Pohnpei"],["Pacific/Port_Moresby","Port_Moresby"],["Pacific/Rarotonga","Rarotonga"],["Pacific/Saipan","Saipan"],["Pacific/Tahiti","Tahiti"],["Pacific/Tarawa","Tarawa"],["Pacific/Tongatapu","Tongatapu"],["Pacific/Wake","Wake"],["Pacific/Wallis","Wallis"]],"Australia"=>[["Australia/Adelaide","Adelaide"],["Australia/Brisbane","Brisbane"],["Australia/Broken_Hill","Broken_Hill"],["Australia/Currie","Currie"],["Australia/Darwin","Darwin"],["Australia/Eucla","Eucla"],["Australia/Hobart","Hobart"],["Australia/Lindeman","Lindeman"],["Australia/Lord_Howe","Lord_Howe"],["Australia/Melbourne","Melbourne"],["Australia/Perth","Perth"],["Australia/Sydney","Sydney"]]];
        $dateTime=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));

        if(!$this->timezone) $this->timezone = 'America/Bogota';

        foreach($timeZones as $k=>&$zone){
            foreach($zone as &$timeZone){
                $timeZone[2]=$dateTime->setTimezone(new \DateTimeZone($timeZone[0]))->format("H:i (h:ia)");
                $timeZone[3]=false;
                if($timeZone[0]==$this->timezone){
                    $timeZone[3]=true;
                }
            }
        }

        return $timeZones;

    }

    public function getElectivesLeft(){
        $electives=Level::where("type","elective")->where("enabled",1)->get();

        return $electives->diff($this->getElectives());
    }

    public function getElectives(){
        return $this->levels()->where("type","elective")->where("enabled",1)->where("paid",1)->get();
    }

    public function emptyGoogleToken(){
        User::where('id',$this->id)->update(['google_token'=>NULL, 'refresh_google_token'=>NULL]);
        $this->google_token = NULL;
        $this->refresh_google_token = NULL;
        return false;
    }

    public function getGoogleToken(){
        $user = User::where('id',$this->id)->first();
        $this->google_token = $user->google_token;
        if(!isset($user->google_token)){
            return false;
        }
        return json_decode($user->google_token,true);
    }

    public function requiredElectives(){
        if($this->user_level<3){
            return 0;
        } elseif($this->user_level==3){
            return 5;
        } elseif($this->user_level==4){
            return 15;
        } elseif($this->user_level==5){
            return 30;
        } elseif($this->user_level==6){
            return 50;
        } elseif($this->user_level==7){
            return 70;
        } elseif($this->user_level==8){
            return 100;
        } elseif($this->user_level==9){
            return 130;
        }
    }

    public function getProgress(){
        $subscriptionType=session("current_subscription");
        $completed = new \stdClass;
        $completed->level=$this->user_level;
        $completed->electives=0;
        $completed->core=0;
        $completed->core_required=0;
        $completed->percentage=0;
        $completed->next_lesson=false;
        $completed->current_level=false;

        if($subscriptionType=="real"){
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type",$subscriptionType)->first();
        } else {
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type","<>","real")->where("type","<>","intros")->first();
        }


        if(!$level){
            $completed->user_percentage=100;
            $completed->percentage=100;
            return $completed;
        }
        $completed->current_level=$level;

        $user_lessons=$this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$level->id)->get();
        $level_lessons=Lesson::where("enabled",1)->where("level_id",$level->id)->get();

        $completed->core=$user_lessons->count();
        $completed->core_required=$level_lessons->count();

        $user_lessons=$user_lessons->unique("id");
        $completed->next_lesson=$level_lessons->diff($user_lessons)->first();

        //without elective
        if($this->user_level<3){
            $completed->level=($user_lessons->count()/($level_lessons->count()));

        } else {
            //With elective -> 15 per level
            $electives=Level::where("type","elective")->where("enabled",1)->get();
            $user_electives=collect();
            foreach($electives as $elective){
                $user_electives=$user_electives->merge($this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$elective->id)->get());
            }

            $user_electives=$user_electives->unique("id");
            $completed->electives=$user_electives->count();
            //$completed->electives=$user_electives->count()-($this->requiredElectives()*($this->user_level-4));

            if($completed->electives<0){
                $completed->electives=0;
            } elseif($completed->electives>$this->requiredElectives()){
                $completed->electives=$this->requiredElectives();
            }

            $completed->level=($user_lessons->count()+$completed->electives)/($level_lessons->count()+$this->requiredElectives());

        }

        $completed->user_percentage=$this->getLevelProgress($completed->level);

        $completed->percentage=intval($completed->level*100);


        if($completed->level==1){
            $completed->level=0.9;
        }

        $completed->level=$this->user_level+floor($completed->level*10)/10;


        return $completed;

    }

    public function getProgressInTeacher($subscriptionType){
        $completed = new \stdClass;
        $completed->level=$this->user_level;
        $completed->electives=0;
        $completed->core=0;
        $completed->core_required=0;
        $completed->percentage=0;
        $completed->next_lesson=false;
        $completed->current_level=false;

        if($subscriptionType=="real"){
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type",$subscriptionType)->first();
        } else {
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type","<>","real")->where("type","<>","intros")->first();
        }


        if(!$level){
            $completed->user_percentage=100;
            $completed->percentage=100;
            return $completed;
        }
        $completed->current_level=$level;

        $user_lessons=$this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$level->id)->get();
        $level_lessons=Lesson::where("enabled",1)->where("level_id",$level->id)->get();

        $completed->core=$user_lessons->count();
        $completed->core_required=$level_lessons->count();

        $user_lessons=$user_lessons->unique("id");
        $completed->next_lesson=$level_lessons->diff($user_lessons)->first();

        //without elective
        if($this->user_level<=3){
            $completed->level=($user_lessons->count()/($level_lessons->count()));

        } else {
            //With elective -> 15 per level
            $electives=Level::where("type","elective")->where("enabled",1)->get();
            $user_electives=collect();
            foreach($electives as $elective){
                $user_electives=$user_electives->merge($this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$elective->id)->get());
            }

            $user_electives=$user_electives->unique("id");
            $completed->electives=$user_electives->count()-($this->requiredElectives()*($this->user_level-4));

            if($completed->electives<0){
                $completed->electives=0;
            } elseif($completed->electives>$this->requiredElectives()){
                $completed->electives=$this->requiredElectives();
            }

            $completed->level=($user_lessons->count()+$completed->electives)/($level_lessons->count()+$this->requiredElectives());

        }

        $completed->user_percentage=$this->getLevelProgress($completed->level);

        $completed->percentage=intval($completed->level*100);


        if($completed->level==1){
            $completed->level=0.9;
        }

        $completed->level=$this->user_level+floor($completed->level*10)/10;


        return $completed;

    }

    public function getLevelProgress($completed_level){

        $subscriptionType=session("current_subscription");
        if($subscriptionType=="real"){
            $levels=Level::where("enabled",1)->where("type",$subscriptionType)->count();
        } else {
            $levels=Level::where("enabled",1)->where("type","<>","real")->where("type","<>","intros")->count();
        }
        $progress=floor(100*$this->user_level/$levels)+floor((100/$levels)*$completed_level);
        return $progress;
    }



    public function checkGoogleToken(){
        if(!$this->getGoogleToken()){
            return false;
        }

        try {
            $googleClient=GoogleClient::getGoogleClient();
            $service = new \Google_Service_Oauth2($googleClient);
            $googleClientResponse=$service->userinfo->get();

        } catch (\Exception $e) {
            Log::error("Error google token: ".$e->getMessage());
            return false;
        }
        return $googleClientResponse;
    }

    public function getCurrentRol(){
        $currentRoll = session('current_rol');
        if(!$currentRoll){
            try {
                $currentRoll=$this->roles->first()->id;
                session(['current_rol' => $currentRoll]);
            } catch (\Exception $e){
                session(['current_rol' => 2]);
                $currentRoll=2;
                $this->attachRole(Role::where("name","student")->first());
            }

        };

        if(!Role::where("id",$currentRoll)->first()->users()->where("id",$this->id)->first()){
            session(['current_rol' => null]);
            return $this->getCurrentRol();
        };


        $currentRoll = Role::where("id", $currentRoll)->first();

        if(!$this->hasRole($currentRoll->name)) {
            session(["current_rol"=>null]);
        }


        return $currentRoll;

    }

    /**
     * Get PaymentHistory FROM ChargeBee.
     * @param $limit => Optional variable that get limit variable in the history.
     * @param $skip => Optional variable that skip payment history.
     * @return \ChargeBee_ListResult | array
    */
    public function getPaymentHistory($skip=null,$limit=3){
        try {
            $all = \ChargeBee_Transaction::all([
                "offset" => $skip,
                "limit" => $limit,
                "status[is]" => "success",
                "customerId[is]" => $this->chargebee_id,
                "sortBy[desc]" => "date"
            ]);

            $sources =[];

            foreach($all as $transaction){
                try {
                    $sources[$transaction->transaction()->paymentSourceId] = [];
                } catch (\Exception $e){
                    Error::reportError('No paymentSource ID',$e->getLine(),$e->getMessage());
                }

            }
            foreach($sources as $k=>$source){
                try {
                    $sources[$k] = \ChargeBee_PaymentSource::retrieve($k)->paymentSource();
                } catch (\Exception $e){
                    Error::reportError('Error retriving payment methods',$e->getLine(),$e->getMessage());
                }
            };
            $all->sources = $sources;

        } catch (\Exception $e){
            Error::reportError('Error getting Payment History'.$e->getLine(),$e->getMessage());
            return [];
        }

        return $all;
    }

    public function getPaymenthHistory(){
        try {
            $all = \ChargeBee_Transaction::all([
//                "offset" => $skip,
//                "limit" => $limit,
                "status[is]" => "success",
                "customerId[is]" => $this->chargebee_id,
                "sortBy[desc]" => "date"
            ]);

            $sources =[];

            foreach($all as $transaction){
                try {
                    $sources[$transaction->transaction()->paymentSourceId] = [];
                } catch (\Exception $e){
                    Error::reportError('No paymentSource ID',$e->getLine(),$e->getMessage());
                }

            }
            foreach($sources as $k=>$source){
                try {
                    $sources[$k] = \ChargeBee_PaymentSource::retrieve($k)->paymentSource();
                } catch (\Exception $e){
                    Error::reportError('Error retriving payment methods',$e->getLine(),$e->getMessage());
                }
            };
            $all->sources = $sources;

        } catch (\Exception $e){
            Error::reportError('Error getting Payment History'.$e->getLine(),$e->getMessage());
            return [];
        }

        return $all;
    }

    public function getLastActive(){
        if($this->subscribed()){
            return false;
        }


        return $this->last_unlimited_subscription;

    }

    /**
     * Get subscriptions by ends order
     * 1. Get last subscription ends
     * @return Subscription
     * */
    public function getLastSubscription(){
        $subscription = $this->subscriptions()->orderBy('ends_at','desc')->first();
        if(!$subscription){
            return Subscription::getDefaultSubscription();
        }
        return $subscription;
    }

    public function getLastSubscriptionType(){
        if(!isset($this->last_unlimited_subscription)){
            return false;
        }

        if(in_array($this->last_unlimited_subscription,["baselang_dele","baselang_dele_trial","baselang_dele_test","medellin_dele"])){
            return "dele";
        } elseif(in_array($this->last_unlimited_subscription,["baselang_dele_realworld","baselang_dele_realworld_trial"])) {
            return "dele_real";
        }
        return "real";
    }

    /**
     * User can pause current subscription
     * @return boolean
     */
    public function canPauseSubscription(){
        $current_subscription = $this->getSubscriptionAttribute();
        return ($current_subscription->plan->can_pause && !in_array($current_subscription->status,['paused','cancelled','in_trial','future']) && !$current_subscription->pause);
    }

     public function getNextChargeAttribute(){
        $payment=new \stdClass();
        $payment->date=false;
        $payment->amount=0;
        try {
            if($this->is_subscribed){
                $payment->date = $this->subscription->next_billing;
                $payment->amount=$this->subscription->change?$this->subscription->future->price:$this->subscription->plan->price;
            } elseif($this->is_pending) {
                $payment->date = $this->subscription->next_billing;
                $payment->amount=$this->subscription->change?$this->subscription->future->price:$this->subscription->plan->price;
            }

            $next_payment = $this->scheduledPayments->sortBy('payment_date')->first();

            if($next_payment){
                if($payment->date){
                    $payment->date=$next_payment->payment_date->format('U')>$payment->date->format('U')?$next_payment->payment_date:$payment->date;
                    $payment->amount=$next_payment->amount;
                } else {
                    $payment->date=$next_payment->payment_date;
                    $payment->amount=$next_payment->amount;
                }
            }
        } catch (\Exception $e){
            Error::reportError('Erorr on next charge attribute',$e->getLine(),$e->getMessage());
        }
        
        return $payment;

    }

    /**
     * User can cancel current subscription
     * @return boolean
     */
    public function canCancelSubscription(){
        $current_subscription = $this->getSubscriptionAttribute();
        return ($current_subscription->ends_at>gmdate('Y-m-d H:i:s') && !in_array($current_subscription->status,['cancelled','non_renewing','paused'])) && ($this->subscription->next_billing->format('Y-m-d')>=gmdate('Y-m-d'));
    }

    /**
     * Know if subscription is future
     */
    public function getIsPendingAttribute(){
        if($this->subscription->status=='future'){
            return true;
        };

        return false;
    }

    public function getIsSubscribedAttribute(){
        if(!isset($this->cache_is_subscribed)){
            $this->cache_is_subscribed=$this->isSubscribed();
        }
        return $this->cache_is_subscribed;
    }

    /**
     * Remove Last Plan when user don't have subscription
     */
    public function clearLastPlan(){
        $this->last_plan=null;
        $this->secureSave();
    }

    /**
     * @param boolean $refresh Refresh Data FROM ChargeBee
     * @return boolean
    */
    public function isSubscribed($refresh=false){
        if($refresh){
            $this->refreshInformation();
        }
        //$subscription=$this->subscriptions()->where("ends_at",">=",gmdate('Y-m-d H:i:s'))->whereIn('status',["non_renewing","active","in_trial"])->first();
        $subscription=$this->subscriptions()->where("ends_at",">=",gmdate('Y-m-d'))->whereIn('status',["non_renewing","active","in_trial"])->first();
        if($subscription){
            return true;
        }

        $this->clearLastPlan();
        return false;
    }

     /*
     * Relation subscriptions one to many
     */
    public function scheduledPayments()
    {
        return $this->hasMany('App\Models\ScheduledPayments')->where('payment_date','>',gmdate('Y-m-d'));
    }

    /*
     * Get time left to start the class
     * return integer
     */
    public function getSubscriptionAttribute(){

        if(!isset($this->cache_subscription)){
            $this->cache_subscription = $this->getCurrentSubscription();
        }
        return $this->cache_subscription;
    }

    public function getCurrentSubscriptionType(){
        $inmersion = $this->buy_inmersions->first();

        if($inmersion){
            $subscription = $this->getCurrentSubscription();
            if($subscription){
                if(in_array($subscription->plan_name,["baselang_dele","baselang_dele_trial","baselang_dele_test","medellin_dele"])){
                    return "dele";
                }
            }
            return "real";
        }

        $subscription=$this->getCurrentSubscription();
        if(!$subscription){
            return false;
        }

        if(in_array($subscription->plan_name,["baselang_dele","baselang_dele_trial","baselang_dele_test","medellin_dele"])){
            return "dele";
        } elseif(in_array($subscription->plan_name,["baselang_dele_realworld","baselang_dele_realworld_trial"])) {
            return "dele_real";
        }
        return "real";
    }

    public function getCurrentPendingSubscription(){
        $subscription=Subscription::where("user_id",$this->id)->where("ends_at",">=",gmdate("Y-m-d"))->first();

        if(!$subscription){
            return false;
        }
        return $subscription;
    }

    public function refreshSubscriptions(){
        if(!$this->chargebee_id){
            Error::reportError('User without chargebee ID');
            return false;
        }

        $plans = Plan::where('type','immersion')->get();

        $subscriptions = \ChargeBee_Subscription::all([
            "customerId[is]" => $this->chargebee_id
        ]);

        if(count($subscriptions)>0){
            $this->subscriptions()->delete();
        }
        $duplicate = 0;
        foreach($subscriptions as $k=>$sub){
            if(!isset($this->last_unlimited_subscription)){
                $this->last_unlimited_subscription=$sub->subscription()->planId;
                User::where("id",$this->id)->update(["last_unlimited_subscription"=>$sub->subscription()->planId]);
            }
            if($sub->subscription()->currentTermEnd>gmdate('U')){
                $duplicate++;
            }

            if($duplicate==2){
                Error::reportError('User with more subscriptions: '.$this->email);
            }

            $subscription = new Subscription();
            $subscription->subscription_id=$sub->subscription()->id;
            $subscription->plan_name=$sub->subscription()->planId;
            $subscription->status=$sub->subscription()->status;
			
			if(!$this->timezone){
				$time_zone_to = "UTC";
			}
			else{
				$time_zone_to = $this->timezone;                    
			}
			$time_zone_from = "UTC";
			
			$startDate = "";
            $endDate = "";
			if($sub->subscription()->startDate){
				$startDate = \DateTime::createFromFormat('U',$sub->subscription()->currentTermStart?$sub->subscription()->currentTermStart:$sub->subscription()->trialStart?$sub->subscription()->trialStart:$sub->subscription()->startDate)->format('Y-m-d H:i:s');
            }
            elseif($sub->subscription()->status == 'cancelled')
            {
                $startDate = "";
            }
            else 
            {
                $startDate = \DateTime::createFromFormat('U',$sub->subscription()->currentTermStart?$sub->subscription()->currentTermStart:$sub->subscription()->trialStart)->format('Y-m-d H:i:s');
            }
			$start_date = new \DateTime($startDate, new \DateTimeZone($time_zone_from));
			$start_date->setTimezone(new \DateTimeZone($time_zone_to));
			$subscription->starts_at=$start_date;
			
			if($sub->subscription()->startDate){
				$startDate = \DateTime::createFromFormat('U', $sub->subscription()->startDate);
				$endDate = date_add($startDate, date_interval_create_from_date_string(($sub->subscription()->billingPeriod - 1).' days'));	
				$endDate = $endDate->getTimestamp();
				$endDate = \DateTime::createFromFormat('U',$sub->subscription()->currentTermEnd?$sub->subscription()->currentTermEnd:$sub->subscription()->trialEnd?$sub->subscription()->trialEnd:$endDate)->format('Y-m-d H:i:s');	
            }
            elseif($sub->subscription()->status == 'cancelled')
            {
                $endDate = "";
            }
			else {
				$endDate = \DateTime::createFromFormat('U',$sub->subscription()->currentTermEnd?$sub->subscription()->currentTermEnd:$sub->subscription()->trialEnd)->format('Y-m-d H:i:s');	
			}
			$end_date = new \DateTime($endDate, new \DateTimeZone($time_zone_from));
			$end_date->setTimezone(new \DateTimeZone($time_zone_to));		
			$subscription->ends_at=$end_date;	
			
            $subscription->period_unit=$sub->subscription()->billingPeriodUnit;
            $subscription->current_subscription=$sub->subscription()->cfCurrentSubscription;
            $subscription->user()->associate($this->id);
            if($sub->subscription()->pauseDate){
				$pauseDate = \DateTime::createFromFormat('U',$sub->subscription()->pauseDate)->format('Y-m-d H:i:s');
				$pause_date = new \DateTime($pauseDate, new \DateTimeZone($time_zone_from));
				$pause_date->setTimezone(new \DateTimeZone($time_zone_to));
                $subscription->pause=$pause_date;
            };
            if($sub->subscription()->resumeDate){
				$resumeDate = \DateTime::createFromFormat('U',$sub->subscription()->resumeDate)->format('Y-m-d H:i:s');
				$resume_date = new \DateTime($resumeDate, new \DateTimeZone($time_zone_from));
				$resume_date->setTimezone(new \DateTimeZone($time_zone_to));
                $subscription->resume=$resume_date;
            };
            if($sub->subscription()->nextBillingAt){
                $unix_timestamp = $sub->subscription()->nextBillingAt;
                $datetime = new \DateTime("@$unix_timestamp");
                $date_time_format = $datetime->format('Y-m-d H:i:s');
                $next_billing_date = new \DateTime($date_time_format, new \DateTimeZone($time_zone_from));
                $next_billing_date->setTimezone(new \DateTimeZone($time_zone_to));
                $subscription->next_billing = $next_billing_date;

                //next_payment amount get api
                $sub_estimate = \ChargeBee_Estimate::renewalEstimate($sub->subscription()->id);
                $next_payment = $sub_estimate->estimate()->invoiceEstimate->amountDue;
                if($next_payment){
                    $subscription->next_payment = $next_payment/100;
                }
            }
            if($sub->subscription()->hasScheduledChanges){
                $future_subscription = \ChargeBee_Subscription::retrieveWithScheduledChanges($sub->subscription()->id);
                $subscription->change = $future_subscription->subscription()->planId;
            }

            $subscription->secureSave();

        }

        return true;
    }

    public function refreshSubscriptionSession(){
        $current_subscription=$this->getCurrentSubscriptionType();
        $rol=$this->getCurrentRol();
        if($rol && $rol->name=="coordinator"){
            return true;
        }

        if($current_subscription=="dele_real"){
            return true;
        }

        if(!$current_subscription){
            $current_subscription='real';
        }

        session(['current_subscription' => ($current_subscription=="real"?"real":"dele")]);
        return true;
    }

    /**
     * Get subscriptions that ends in the future
     * 1. If have multiples, then send error report
     * 2. Get first subscription in query
     * @return Subscription
     * */
    public function getCurrentSubscription(){
        $subscriptions = $this->subscriptions->where('ends_at','>',gmdate('Y-m-d H:i:s'))->where('status','<>','cancelled');
        if($subscriptions->count()>1){
            Error::reportError('User With Multiple Subscriptions');
        }
        $subscription = $subscriptions->first();
        if(!$subscription){
            return $this->getLastSubscription();
        }

        if($subscription->change && $subscription->change!=$subscription->plan->name){
            $subscription->future = Plan::where('name',$subscription->change)->first();
        } else {
            $subscription->change=null;
        }
        if(!$subscription->plan_name){
            Error::reportError('Plan does not exist: '.$subscription->plan_name);
            $subscription->plan = Plan::getDefaultPlan();
            $subscription->plan->converted_type = Subscription::getConvertedType();
        } else {
            $subscription->plan->converted_type = Subscription::getConvertedType($subscription->plan->type);
        }

        if($subscription->plan_name == 'baselang_hourly' && $subscription->status == 'future')
        {
            $subscription->plan_name = $this->last_unlimited_subscription;
            $subscription->plan =  Plan::where('plan_id',$this->last_unlimited_subscription)->first();
        }
        return $subscription;
    }

    public function subscriptionAdquired(){
        $subscription=Subscription::where("user_id",$this->id)->orderBy("id", "ASC")->first();

        if($subscription && $subscription->status=="future"){
            $plan = Plan::where('plan_id',$this->last_unlimited_subscription)->first();
            $subscription->plan->name=$plan->name;
        }

        return $subscription;
    }

    public function isSchoolStudent(){
        $subscription=$this->getCurrentSubscription();

        if($subscription && $subscription->status=="future"){
            $plan = Plan::where('plan_id',$this->last_unlimited_subscription)->first();
            $subscription->plan->name=$plan->name;
        }

        if($subscription && in_array($subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
            return true;
        }

        return false;
    }

    public function isInmersionStudent(){
        $subscription = $this->subscriptions()->whereIn('plan_name',['grammarless-online-1000paymentplan','grammarless-online-900', 'grammarless-medellin-600','grammarless-medellin-1200'])->where('ends_at','>',gmdate('Y-m-d H:i:s'))->first();
        if($subscription){
            $inmersion = BuyInmersion::where('user_id',$this->id)->where('inmersion_end','>=',gmdate('Y-m-d'))->first();
            if(!$inmersion){
                $start = \DateTime::createFromFormat('Y-m-d H:i:s',$subscription->starts_at);
                $buy_immersion = BuyInmersion::create(['user_id'=>$this->id,'teacher_id'=>1,'total_price'=>0,'inmersion_start'=>$start,'inmersion_end'=>$subscription->ends_at,'hour_format'=>'AM','second_payment_date'=>gmdate("Y-m-d", strtotime("-1 months")),'status'=>1,'location_id'=>2]);
                return $buy_immersion;
            }
            return $inmersion;
        };
        $start = \DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'));
		$inmersion = BuyInmersion::where('user_id',$this->id)->where('inmersion_end','>=',gmdate('Y-m-d'))->first();
        if($inmersion){
            return $inmersion;
        }

        return false;
    }

    public function isInmersionRunning(){
        if($this->isInmersionStudent()){
            $inmersion = BuyInmersion::where('user_id',$this->id)->where('inmersion_start','<=',gmdate('Y-m-d'))->where('inmersion_end','>=',gmdate('Y-m-d'))->first();
            if($inmersion) {
                return $inmersion;
            }
        }
        return false;
    }

    public function isOnlineInmersionStarted(){
        if($this->isInmersionStudent() && $this->isInmersionStudent()->location_id==2){
            $inmersion = $this->paid_inmersions()->first();
            if($inmersion){
                $start = \DateTime::createFromFormat('Y-m-d',$inmersion->inmersion_start)->add(new \DateInterval('P3D'));
                if(gmdate("Y-m-d")>$start->format('Y-m-d')){
                    return true;
                }
            }
        }
        return false;
    }

    public function getDiscount($referral_code){
		try 
		{
            $discounts = \ChargeBee_Coupon::all(array(
                "status[is]" => "active"
                ));
              foreach($discounts as $discount)
              {
                $coupon = $discount->coupon();
                if($coupon->id == $referral_code)
                    {
                        $discount_amount = $coupon->discountAmount;
                        return $discount_amount;
                    }
              }
        } catch (\Exception $e){
            Log::error("Error Paymenth History: ".$e->getMessage());
            return false;
        }
		
        return false;
    }

    public function showGetFreeTime(){
        $trans_count_flag = false;
        try {
            $transactions = \ChargeBee_Transaction::all([
                "customerId[is]" => $this->chargebee_id
            ]);
            $trans_count_flag = (count($transactions) > 1) ? true : false;
        } catch (\Exception $e){
            Error::reportError('Error in fetching user transactons',$e->getLine(),$e->getMessage());
        }

        if($this->last_unlimited_subscription && in_array($this->last_unlimited_subscription,["baselang_149","baselang_149_trial","baselang_129","baselang_129_trial","baselang_99","baselang_99_trial","baselang_hourly","baselang_dele","baselang_dele_trial"]) && $trans_count_flag){
            return true;
        }
        
        return false;
    }

    public function isReferralEnabled(){
        $chk_referral=UserFreeDays::where("user_id",$this->id)->get();
        if(count($chk_referral) > 0){
            return true; 
        }
        return false;
    }

    public function isInmersionActive(){
        $inmersion = $this->paid_inmersions()->first();
        if($inmersion){
            $start = \DateTime::createFromFormat('Y-m-d',$inmersion->inmersion_start);
            if(gmdate("Y-m-d")>=$start->format('Y-m-d') && gmdate("Y-m-d")<=$inmersion->inmersion_end){
                return $inmersion;
            }
        }

        return false;
    }

    public function isOnlineGrandfatheredPlanCancelledOrPaused()
    {
        $plan = $this->subscription->plan->plan_id;
        $sub_status = $this->subscription->status;
        if(in_array($plan, ["baselang_99","baselang_129"]) && in_array($sub_status, ["cancelled","paused"]))
        {
            return true;
        }
        return false;
    }

    public function getOnlineRWPlan()
    {
        $online_plans = Plan::getByPlanId("baselang_149");
        return $online_plans[0];
    }

    /**
     * Get prebook
     * @return Prebook
     */
    public function getPrebook(){
        if(isset($this->prebook_cache)){
            return $this->prebook_cache;
        }
        return $this->products()->where('product','prebook')->where('status',1)->where('limit_date','>=',gmdate('Y-m-d').' 23:59:59')->first();
    }

    public function isInmersionFinalized(){
        $inmersion = $this->buy_inmersions->first();

        if($inmersion && gmdate("Y-m-d")<=$inmersion->inmersion_end){
            return $inmersion;
        }

        return false;
    }

    /**
     * Relation about products buyed by the user
    */
    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function subscribed($verified=false){
        $end_limit=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P2D"))->format("Y-m-d");
        $subscription=Subscription::where("user_id",$this->id)->where("ends_at",">=",$end_limit)->first();

        if(!$subscription && !$verified){
            $this->updateSubscriptionInfo();
            return $this->subscribed(true);
        } elseif($subscription){
            if($subscription->status=="cancelled" && $subscription->ends_at<gmdate("Y-m-d")){
                return false;
            }

            if($this->check_landing_date){
                $limit=\DateTime::createFromFormat("Y-m-d",$subscription->starts_at)->sub(new \DateInterval("P5D"))->format("Y-m-d");

                if(gmdate("Y-m-d")>=$limit){
                    return true;
                }

                return false;
            }

            return true;
        };

        if($this->last_unlimited_subscription=="baselang_99" || $this->last_unlimited_subscription=="baselang_99_trial"){
            User::where("id",$this->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
        }

        if($this->last_unlimited_subscription=="baselang_129" || $this->last_unlimited_subscription=="baselang_129_trial"){
            User::where("id",$this->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
        }

        if($this->active_locations){
            if(gmdate("Y-m-d")>=$this->active_locations->date_to_schedule){

                $route = \Route::currentRouteName();

                if($route=="classes_in_person_new" || $route=="classes_user_new_teacher" || $route=="calendar_in_person_all" || $route=="calendar_in_person_teacher" || $route=="choose_teacher" || $route=="confirm_classes" || $route=="save_classes" || $route=="booked_classes" || $route=="classes"){
                    return true;
                }

                if($route=="classes_new" || $route=="classes_new_teacher" || $route=="calendar_all" || $route=="calendar_teacher" || $route=="cancel_classes"){
                    return true;
                }
            }
        }

        return false;
    }

    public function createChargebeeIdId(){

        $result = \ChargeBee_Customer::create([
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
        ]);

        Log::info("Customer Create For: ".$this->email." Status: ". var_export($result->success,true));

        return $result->customer;

    }

    public function addFreeDays($days=false){
        $this->updateSubscriptionInfo();

        if(!$days){
            return false;
        }

        $current_subscription=$this->getCurrentSubscription();
        try {
            if(!$current_subscription){
                if($this->last_unlimited_subscription=="baselang_hourly"){
                    $this->last_unlimited_subscription="baselang_149";
                    User::where("id",$this->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
                }

                Subscription::create(["status"=>"cancelled","user_id"=>$this->id,"subscription_id"=>"BaseLang","plan_name"=>$this->last_unlimited_subscription,"starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P".$days."D"))->format("Y-m-d")]);

                $active_location = $this->active_locations;
                if($active_location){
                    $active_location->update(['date_to_schedule'=>\DateTime::createFromFormat('Y-m-d',$active_location->date_to_schedule)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'trial_payday'=>\DateTime::createFromFormat('Y-m-d',$active_location->trial_payday)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'activation_day'=>\DateTime::createFromFormat('Y-m-d',$active_location->activation_day)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d')]);
                }

            } else {

                if($current_subscription->status=="future"){

					$start_date=$current_subscription->starts_at->add(new \DateInterval("P".$days."D"))->format("Y-m-d");

                    Log::info('Adding free days for: '.$this->email.' with start_date '.var_export($start_date,true). "current subscription ".var_export($current_subscription,true));

                    $result = \ChargeBee_Subscription::cancel($current_subscription->subscription_id);
                    Log::info('Canceling Subscription For: '.$this->email.' '.var_export(isset($result->message)?$result->message:$result,true));

                    $dateTime = new \DateTime($start_date);
					$start_date_unix = $dateTime->format('U');
					$result = \ChargeBee_Subscription::createForCustomer($this->chargebee_id,[
						 "planId" => $current_subscription->plan_name,
                         "startDate" => $start_date_unix,
                         "trialEnd" => 0
					]);
					
					if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    Subscription::where("id",$current_subscription->id)->delete();

                    $active_location = $this->active_locations;
                    if($active_location){
                        $active_location->update(['date_to_schedule'=>\DateTime::createFromFormat('Y-m-d',$active_location->date_to_schedule)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'trial_payday'=>\DateTime::createFromFormat('Y-m-d',$active_location->trial_payday)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'activation_day'=>\DateTime::createFromFormat('Y-m-d',$active_location->activation_day)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d')]);
                    }

                } else {
                    //active or non-renewing or cancelled
                    $start_date=$current_subscription->ends_at->add(new \DateInterval("P".$days."D"))->format("Y-m-d");
					if($current_subscription->status=="cancelled"){
                        $s_date = new \DateTime(gmdate("Y-m-d"));
						$start_date = $s_date->add(new \DateInterval("P".$days."D"))->format("Y-m-d");
					}
                    Log::info('Adding free days for Active: '.$this->email.' with start_date '.var_export($start_date,true). "current subscription ".var_export($current_subscription,true));
                    if($start_date==gmdate("Y-m-d")){
						$s_date = new \DateTime($start_date);
                        $start_date = $s_date->add(new \DateInterval("P1D"))->format("Y-m-d");
                    }
					
					if($current_subscription->status!="cancelled"){
						$result = \ChargeBee_Subscription::cancel($current_subscription->subscription_id);
						if(isset($result->success)){
							Log::info('Canceling Subscription For: '.$this->email.' '.var_export($result->success,true).' '.$current_subscription->subscription_id);
						}
					}
                
					$dateTime = new \DateTime($start_date);
					$start_date_unix = $dateTime->format('U');
					$result = \ChargeBee_Subscription::createForCustomer($this->chargebee_id,[
						 "planId" => $current_subscription->plan_name,
                         "startDate" => $start_date_unix,
                         "trialEnd" => 0
					]);

                    $active_location = $this->active_locations;
                    if($active_location){
                        $active_location->update(['date_to_schedule'=>\DateTime::createFromFormat('Y-m-d',$active_location->date_to_schedule)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'trial_payday'=>\DateTime::createFromFormat('Y-m-d',$active_location->trial_payday)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d'), 'activation_day'=>\DateTime::createFromFormat('Y-m-d',$active_location->activation_day)->add(new \DateInterval('P'.$days.'D'))->format('Y-m-d')]);
                    }
                    
                    if($current_subscription->plan_name=="baselang_hourly")
                    {
                        $change_date=$current_subscription->ends_at->add(new \DateInterval("P1D"))->format("Y-m-d");
                        ScheduledChanges::create(["user_id"=>$this->id,"plan"=>$current_subscription->plan_name,"change_date"=>$change_date]);
                    }
                    User::where("id",$this->id)->update(["last_unlimited_subscription"=>$current_subscription->plan_name]);

                    Subscription::where("id",$current_subscription->id)->delete();
                }
                
                if($current_subscription->plan_name=="baselang_hourly" && $current_subscription->status!="active") {
                        $this->last_unlimited_subscription="baselang_149";
                        User::where("id",$this->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
                }

                if($this->pause_account && $this->pause_account->activation_day){
                    $pause_date=\DateTime::createFromFormat("Y-m-d",$this->pause_account->activation_day)->add(new \DateInterval("P".$days."D"))->format("Y-m-d");
                    $this->pause_account->update(["activation_day"=>$pause_date]);
                }

            }
        } catch (\Exception $e){
            Log::error("Error Adding Free Days: ".$e->getMessage());
            return false;
        }
        $this->updateSubscriptionInfo();
        return true;
    }

    public function verifyRole(){
        if(count($this->roles)>0){
            return true;
        };

        $student=Role::where("name","student")->first();
        $this->attachRole($student);

        return true;
    }

    public function updateChargebeeInfo($create=false){
        try {
            if(empty($this->chargebee_id) || !isset($this->chargebee_id)){

                $results = \ChargeBee_Customer::all([
                    "email[is]" => $this->email,
                ]);

                if($results->count()>0){
                    $results->rewind();
                    $this->chargebee_id = $results->current()->customer()->id;
                    $this->payment_method_token = $results->current()->customer()->primaryPaymentSourceId;
                    $result = $this->secureSave();
                    if($results->count()>1){
                        Error::reportError('User with multiple ChargeBee Accounts: '.$this->email);
                    }

                } elseif(!$create) {
                    $this->createChargeBeeCustomer();
                }
            } else {
                try {
                    $result = \ChargeBee_Customer::retrieve($this->chargebee_id);
                    if($result->customer()->email!=$this->email){
                        Error::reportError('User with different Email and ChargeBee ID: '.$this->email.' Found: '.$result->customer()->email);
                    }
                } catch (\Exception $exception){
                    Error::reportError('ChargeBee Exception Retrive User',$exception->getLine(),$exception->getMessage());
                    $this->chargebee_id='';
                    $this->secureSave();
                    $this->updateChargeBeeId();
                }
            }
        } catch (\Exception $exception){
            Error::reportError('Error updating ChargeBee ID',$exception->getLine(),$exception->getMessage());
            return false;
        }
        return $result;
    }

    public function secureSave(){
        $fillables = [];

        foreach($this->fillable as $fillable){
            if(isset($this->toArray()[$fillable])){
                $fillables[$fillable]=$this->toArray()[$fillable];
            }
        };
        $user = \App\User::find($this->id);


        if($user){
            $user->update($fillables);
        } else {
            $user = User::create($fillables);
            $this->id = $user->id;
        }


        return $this;
    }

    public function updateSubscriptionInfo($create=false){

        $customer=$this->updateChargeBeeId($create);

        if(!$customer){
            return false;
        }

        $this->refreshPaymentMethods();
        $this->refreshSubscriptions();

        return true;
    }

    public function checkCredits(){
        Subscription::checkCredits($this->subscription);
        return true;
    }

    /**
     * Create a new ChargeBee Customer
     * @return boolean
     */
    public function createChargeBeeCustomer(){
        try {
            $customer = ['firstName' => $this->first_name,'lastName' => $this->last_name,'email' => $this->email,];

            if(isset($this->referral_email)){
                $customer['cf_referral_student'] = $this->referral_email;
            }

            $result = \ChargeBee_Customer::create($customer);
            $this->chargebee_id = $result->customer()->id;
            $this->payment_method_token = $result->customer()->primaryPaymentSourceId;
            $this->secureSave();
        } catch (\Exception $exception){
            Error::reportError('Error creating ChargeBee Customer',$exception->getMessage(),$exception->getLine());
            return false;
        }
        return true;

    }

    /**
     * 1. Verify if user has ChargeBee ID
     * 1.1. If is new (From External Controller Register) Not create it on ChargeBee
     * 1.2. Else (Created on Admin) Create a ChargeBee account
     * 2. Report IF user email and chargebee email are differents
     * @param boolean $create
     * @return boolean
     */
    public function updateChargeBeeId($create=false){
        try {
            if(empty($this->chargebee_id) || !isset($this->chargebee_id)){

                $results = \ChargeBee_Customer::all([
                    "email[is]" => $this->email,
                ]);

                if($results->count()>0){
                    $results->rewind();
                    $this->chargebee_id = $results->current()->customer()->id;
                    $this->payment_method_token = $results->current()->customer()->primaryPaymentSourceId;
                    $this->secureSave();
                    if($results->count()>1){
                        Error::reportError('User with multiple ChargeBee Accounts: '.$this->email);
                    }

                } elseif(!$create) {
                    $this->createChargeBeeCustomer();
                }
            } else {
                try {
                    $result = \ChargeBee_Customer::retrieve($this->chargebee_id);
                    if($result->customer()->email!=$this->email){
                        Error::reportError('User with different Email and ChargeBee ID: '.$this->email.' Found: '.$result->customer()->email);
                    }
                } catch (\Exception $exception){
                    Error::reportError('ChargeBee Exception Retrive User',$exception->getLine(),$exception->getMessage());
                    $this->chargebee_id='';
                    $this->secureSave();
                    $this->updateChargeBeeId();
                }
            }
        } catch (\Exception $exception){
            Error::reportError('Error updating ChargeBee ID',$exception->getLine(),$exception->getMessage());
            return false;
        }
        return true;
    }

    public function refreshPaymentMethods(){
        try {
            $result = \ChargeBee_Customer::retrieve($this->chargebee_id);
            $this->payment_method_token = $result->customer()->primaryPaymentSourceId;
            if($result->customer()->email!=$this->email){
                Error::reportError('User with different Email and ChargeBee ID: '.$this->email.' Found: '.$result->customer()->email);
            }
            if($this->payment_method_token){
                $result = \ChargeBee_PaymentSource::retrieve($this->payment_method_token);
                if($result->paymentSource()->card){
                    $this->paypal_email=null;
                    $this->card_last_four = $result->paymentSource()->card->last4;
                    $this->pay_image = $result->paymentSource()->card->brand;
                } else {
                    $this->card_last_four=null;
                    $this->paypal_email = $result->paymentSource()->paypal->email;
                    $this->pay_image = $result->paymentSource()->paypal->object;
                }
            } else {
                $this->paypal_email=null;
                $this->card_last_four = null;
                $this->pay_image = null;
            }
        } catch (\Exception $exception){
            $this->chargebee_id='';
            $this->payment_method_token='';
            $this->card_last_four=null;
            $this->pay_image=null;
            $this->secureSave();
            return false;
        }
        $this->secureSave();
        return true;
    }

    public function refreshPaymentMethodsInmMed(){
        $user = User::getCurrent();
        try {
            $result = \ChargeBee_Customer::retrieve($user->chargebee_id);
            $user->payment_method_token = $result->customer()->primaryPaymentSourceId;
            if($result->customer()->email!=$user->email){
                Error::reportError('User with different Email and ChargeBee ID: '.$user->email.' Found: '.$result->customer()->email);
            }
            if($user->payment_method_token){
                $result = \ChargeBee_PaymentSource::retrieve($user->payment_method_token);
                if($result->paymentSource()->card){
                    $user->paypal_email=null;
                    $user->card_last_four = $result->paymentSource()->card->last4;
                    $user->pay_image = $result->paymentSource()->card->brand;
                } else {
                    $user->card_last_four=null;
                    $user->paypal_email = $result->paymentSource()->paypal->email;
                    $user->pay_image = $result->paymentSource()->paypal->object;
                }
            } else {
                $user->paypal_email=null;
                $user->card_last_four = null;
                $user->pay_image = null;
            }
        } catch (\Exception $exception){
            $user->chargebee_id='';
            $user->payment_method_token='';
            $user->card_last_four=null;
            $user->pay_image=null;
            $user->secureSave();
            return false;
        }
        $user->secureSave();
        return true;
    }

    public function levelPrgress(){

        $base_level = $this->levelHistory()->where("updated_at",">=",gmdate("Y-m-d", strtotime("-1 months"))." 00:00:00")->count();
        $progress_level = 0;
        $subscriptionType=session("current_subscription");

        if(!$subscriptionType){

        }

        if($subscriptionType=="real"){
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type",$subscriptionType)->first();
        } else {
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type","<>","real")->where("type","<>","intros")->first();
        }

        if(!$level){
            Log::info("Not Level for '".$this->email."' GET ".var_export($this->user_level,true)." ".var_export($subscriptionType,true));
            return 0;
        }

        $user_lessons=$this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$level->id)->get();
        $level_lessons=Lesson::where("enabled",1)->where("level_id",$level->id)->get();

        $limitDate = \DateTime::createFromFormat("Y-m-d", gmdate("Y-m-d"))->sub(new \DateInterval("P30D"));;
        $core=$user_lessons;
        $core_summary=0;
        foreach($core as $core_lesson){
            if($core_lesson->pivot->finished_at>=$limitDate->format("Y-m-d")." 00:00:00"){
                $core_summary++;
            }
        }


        $core_required=$level_lessons->count();
        //without elective
        if($this->user_level<=3){
            if($core_summary!=0){
                $progress_level=$core_summary/$core_required;
            }

            $progress_level=floor(10*($core_summary)/($core_required))/10;

        } else {

            $elective_required=$this->requiredElectives();
            $elective=0;

            $statistics=Statistics::where("user_id",$this->id)->where("type","Complete_Lesson")->where("data_y","elective")->where("created_at",gmdate("Y-m-d", strtotime("-1 months"))." 00:00:00")->get();
            foreach($statistics as $statistic){
                //check if is completed
                $statistic_elective = $this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$statistic->data_x)->first();
                if($statistic_elective){
                    $elective++;
                }
            }


            $progress_level=floor(10*($core_summary+$elective)/($core_required+$elective_required))/10;

        }




        if($progress_level==1){
            $progress_level=0.9;
        }

        return $progress_level+$base_level;
    }

    public function levelProgressInTeacher($subscriptionType){

        $base_level = $this->levelHistory()->where("updated_at",">=",gmdate("Y-m-d", strtotime("-1 months"))." 00:00:00")->count();
        $progress_level = 0;

        if($subscriptionType=="real"){
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type",$subscriptionType)->first();
        } else {
            $level=Level::where("level_order",$this->user_level)->where("enabled",1)->where("type","<>","real")->where("type","<>","intros")->first();
        }

        if(!$level){
            Log::info("Not Level for '".$this->email."' GET ".var_export($this->user_level,true)." ".var_export($subscriptionType,true));
            return 0;
        }

        $user_lessons=$this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$level->id)->get();
        $level_lessons=Lesson::where("enabled",1)->where("level_id",$level->id)->get();

        $limitDate = \DateTime::createFromFormat("Y-m-d", gmdate("Y-m-d"))->sub(new \DateInterval("P30D"));;
        $core=$user_lessons;
        $core_summary=0;
        foreach($core as $core_lesson){
            if($core_lesson->pivot->finished_at>=$limitDate->format("Y-m-d")." 00:00:00"){
                $core_summary++;
            }
        }


        $core_required=$level_lessons->count();
        //without elective
        if($this->user_level<=3){
            if($core_summary!=0){
                $progress_level=$core_summary/$core_required;
            }

            $progress_level=floor(10*($core_summary)/($core_required))/10;

        } else {

            $elective_required=$this->requiredElectives();
            $elective=0;

            $statistics=Statistics::where("user_id",$this->id)->where("type","Complete_Lesson")->where("data_y","elective")->where("created_at",gmdate("Y-m-d", strtotime("-1 months"))." 00:00:00")->get();
            foreach($statistics as $statistic){
                //check if is completed
                $statistic_elective = $this->lessons()->where("completed",1)->where("enabled",1)->where("level_id",$statistic->data_x)->first();
                if($statistic_elective){
                    $elective++;
                }
            }


            $progress_level=floor(10*($core_summary+$elective)/($core_required+$elective_required))/10;

        }




        if($progress_level==1){
            $progress_level=0.9;
        }

        return $progress_level+$base_level;
    }

    public function getPaypalEmail(){
        return preg_replace("/(?!^).(?=[^@]+@)/", "*", $this->paypal_email);
    }

    public function updatePayMethod(){

        $customer=$this->updateChargebeeInfo();
        if(!$customer){
            $this->updatePayMethod();
            return false;
        }

        foreach($customer->paymentMethods as $paymentMethod){
            if($paymentMethod->default){

                if(isset($paymentMethod->last4)){
                    User::where("id",$this->id)->update(['paypal_email'=>null,'card_last_four'=>$paymentMethod->last4,'payment_method_token'=>$paymentMethod->token,'pay_image'=>$paymentMethod->imageUrl]);
                } else {
                    User::where("id",$this->id)->update(['paypal_email'=>$paymentMethod->email,'card_last_four'=>null,'payment_method_token'=>$paymentMethod->token,'pay_image'=>$paymentMethod->imageUrl]);
                }
            }
        }

        return true;
    }

    public function resetZoom(){
        $this->zoom_id=null;
        $this->save();
        return $this->hasZoom();
    }

    public function getYoutubeID(){
        if($this->youtube_url){
            $match=[];
            preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $this->youtube_url,$match);
            if(isset($match[1])){
                return $match[1];
            }

        }

        return false;


    }

    public function isNew(){

        if($this->created_at){
            if($this->created_at->add(new \DateInterval("P7D"))->format("Y-m-d")>gmdate("Y-m-d")){
                return true;
            };
        }

        return false;
    }

    public function getMedellinGLNextBillingDate(){
        $next_gl_billing_date = false;
        if($this->subscription->starts_at && $this->subscription->plan_name == 'grammarless-medellin-600' && $this->subscription->status == 'future'){
            $next_gl_billing_date =  $this->subscription->starts_at->sub(new \DateInterval("P7D"));
        }

        return $next_gl_billing_date;
    }

    public function hasZoom(){
        $zoomApi = new ZoomAPI();

        if(isset($this->zoom_id)){
            return true;
        }

        try{
            $result=$zoomApi->getUserInfoByEmail($this->email);

            if(isset($result->error)) {
                $result = $zoomApi->createAUser($this->email);
                if (isset($result->error)) {
                    throw new \Exception("No Zoom account for: " . $this->email);
                }
                $this->zoom_id=$zoomApi->getUserInfoByEmail($this->email)->id;
            } else {
                $this->zoom_id=$result->id;
            }
            $this->save();
        } catch (Exception $e){
            Log::error("Error In Zoom: ".$e->getMessage());
            return false;
        }

        return true;
    }

    public static function getCurrent(){
        if(Auth::check()){
            return Auth::user();
        }

        return false;

    }

    public function getEvaluated(){
        $userEvaluation=UserEvaluation::where("teacher_id",$this->id)->get();

        if(count($userEvaluation)==0)
        {
            return false;
        }

        $count=0;
        foreach($userEvaluation as $evaluation)
        {
            $count+=$evaluation->evaluation;
        }

        return round($count/count($userEvaluation));
    }

    public function getEvaluatedStars($evaluation){
        $userEvaluation=UserEvaluation::where("teacher_id",$this->id)->where("evaluation",$evaluation)->count();
        return $userEvaluation;
    }

    public function getEvaluatedCurrent(){
        $userEvaluation=UserEvaluation::where("teacher_id",$this->id)->where("user_id",Auth::id())->first();

        if(!$userEvaluation){
            return false;
        }

        return $userEvaluation;
    }

    /**
     * Statistics saved for this user
     */
    public function statistics()
    {
        return $this->hasMany('App\Models\Statistics');
    }


    public function getFavoriteTeacher(){
        return User::where("id",$this->favorite_teacher)->first();
    }

    public function levelHistory()
    {
        return $this->hasMany('App\Models\UserLevelHistory');
    }

    public function freeDays()
    {
        return $this->hasMany('App\Models\UserFreeDays');
    }

    public function interests()
    {
        return $this->belongsToMany('App\Models\Interests','users_interests','user_id','interest_id');
    }

    public function getCalendar(){
        return $this->hasMany('App\Models\UserCalendar');
    }

    public function teacher_classes(){
        return $this->hasMany('App\Models\Classes','teacher_id','id');
    }

    public function classes()
    {
        return $this->hasMany('App\Models\Classes');
    }

    public function levels()
    {
        return $this->belongsToMany('App\Models\Level','users_levels')->withPivot("paid","transaction_id");
    }

    public function lessons()
    {
        return $this->belongsToMany('App\Models\Lesson','users_lessons')->withPivot("completed","finished_at","homework");
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Models\Subscription');
    }

    public function pause_account()
    {
        return $this->hasOne('App\Models\PauseAccount');
    }

    public function dele_trial_test()
    {
        return $this->hasOne('App\Models\DeleTrialTest');
    }

    public function active_dele_trial()
    {
        return $this->hasOne('App\Models\ActiveDeleTrial');
    }

    public function prebooks()
    {
        return $this->hasMany('App\Models\Prebook');
    }

    public function buy_prebooks()
    {
        return $this->hasMany('App\Models\BuyPrebook')->where("status",1);
    }

    public function log_admin()
    {
        return $this->hasMany('App\Models\LogAdmin');
    }

    public function inmersions_without_paying()
    {
        return $this->hasMany('App\Models\BuyInmersion')->where("status",0);
    }

    public function paid_inmersions()
    {
        return $this->hasMany('App\Models\BuyInmersion')->where("status",1);
    }

    public function buy_inmersions()
    {
        return $this->hasMany('App\Models\BuyInmersion')->orderBy("created_at", "DESC");
    }

    public function inmersion_payment()
    {
        return $this->hasMany('App\Models\InmersionPayment');
    }

    public function active_locations()
    {
        return $this->hasOne('App\Models\ActiveLocation');
    }

    public function teacher_locations()
    {
        return $this->belongsToMany('App\Models\Location','users_location');
    }

    // public function role()
    // {
    //     return $this
    //     ->belongsToMany('App\Role')
    //     ->withTimestamps();
    // }


    public function hasLocation($location_id){
        $location=$this->teacher_locations->where('id',$location_id)->first();
        if($location){
            return $location;
        }
        return false;
    }
}
