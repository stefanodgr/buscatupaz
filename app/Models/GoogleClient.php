<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\User;

class GoogleClient extends Model
{
    public static function getGoogleClient(&$user=false)
    {
        $googleClient = new \Google_Client();
        $config=config("google");
        
        $googleClient->setApplicationName(array_get($config, 'application_name', ''));
        // set oauth2 configs
        $googleClient->setClientId(array_get($config, 'client_id', ''));
        $googleClient->setClientSecret(array_get($config, 'client_secret', ''));
        $googleClient->setRedirectUri(array_get($config, 'redirect_uri', ''));
        $googleClient->setScopes(array_get($config, 'scopes', []));
        $googleClient->setAccessType(array_get($config, 'access_type', 'offline'));
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->setApprovalPrompt("force");
        // set developer key
        $googleClient->setDeveloperKey(array_get($config, 'developer_key', ''));

        if(!$user){
            $user=User::getCurrent();
        }

        if($user->getGoogleToken()){
            try {
                $googleClient->setAccessToken($user->getGoogleToken());
                if($googleClient->isAccessTokenExpired() && $user->refresh_google_token){
                    Log::info('User Change Google Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    $newDataToken=$googleClient->fetchAccessTokenWithRefreshToken($user->refresh_google_token);
                    $user->google_token=json_encode($newDataToken);
                    User::where("id",$user->id)->update(["google_token"=>json_encode($newDataToken)]);
                    Log::info('User Change Google Credentials: '.$user->email.' '.var_export($user->google_token,true));
                }
            } catch (\Exception $e) {
                Log::info('User Change Google Credentials: '.$e->getMessage().' User:'.$user->email.' '.var_export($user->google_token,true));
                if($user->refresh_google_token){
                    $newDataToken=$googleClient->fetchAccessTokenWithRefreshToken($user->refresh_google_token);
                    $user->google_token=json_encode($newDataToken);
                    User::where("id",$user->id)->update(["google_token"=>json_encode($newDataToken)]);
                    Log::info('User Change Google Credentials: '.$user->email.' '.var_export($user->google_token,true));
                    try {
                        $googleClient->setAccessToken($user->getGoogleToken());
                    } catch (\Exception $e) {
                        $user->emptyGoogleToken();
                    }
                } else {
                    $user->emptyGoogleToken();
                }
            }
        }
        
        return $googleClient;
    }
}