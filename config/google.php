<?php

return [
    /*
    |----------------------------------------------------------------------------
    | Google application name
    |----------------------------------------------------------------------------
    */
    'application_name' => 'BaseLang',

    /*
    |----------------------------------------------------------------------------
    | Google OAuth 2.0 access
    |----------------------------------------------------------------------------
    |
    | Keys for OAuth 2.0 access, see the API console at
    | https://developers.google.com/console
    |
    */
    'client_id'       => '135940503756-qoisct9m7s4oeuie6bpe60fv6egi8393.apps.googleusercontent.com',
    'client_secret'   => 'WdDBx1lICm47t3j3X86_qRvP',
    'redirect_uri'    => 'https://web.baselang.com/profile/googlecalendar/link',
    'scopes'          => [Google_Service_Calendar::CALENDAR,Google_Service_Oauth2::USERINFO_EMAIL],
    'access_type'     => 'offline',
    'approval_prompt' => 'auto',

    /*
    |----------------------------------------------------------------------------
    | Google developer key
    |----------------------------------------------------------------------------
    |
    | Simple API access key, also from the API console. Ensure you get
    | a Server key, and not a Browser key.
    |
    */
    'developer_key' => 'AIzaSyDcky2yCmGwGgBkRm4yEeGPUMlLq3s4Y-I',

    /*
    |----------------------------------------------------------------------------
    | Google service account
    |----------------------------------------------------------------------------
    |
    | Set the information below to use assert credentials
    | Leave blank to use app engine or compute engine.
    |
    */
    'service' => [
        /*
        | Example xxx@developer.gserviceaccount.com
        */
        'account' => '',

        /*
        | Example ['https://www.googleapis.com/auth/cloud-platform']
        */
        'scopes' => [],

        /*
        | Path to key file
        | Example storage_path().'/key/google.p12'
        */
        'key' => '',
    ],
];
