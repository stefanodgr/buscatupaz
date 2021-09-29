<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Error extends Model
{
    protected $fillable = [
        'error',
        'line',
        'message',
        'type'
    ];

    public static function getCurrentTypes(){
        return ['Info','Report','Error','Info','Alert'];
    }


    public static function saveError($log,$line='',$message='',$type='Info'){
        $error = new Error;
        $error->error = $log;
        $error->line = $line;
        $error->message = $message;
        $error->type = $type;
        $error->save();
    }

    public static function reportCoordinator($report){
        Error::saveError($report,"","",'Report');
    }

    public static function reportError($log,$line='',$message=''){
        Log::error($log.' '.$message.' '.$line);
        Error::saveError($log,$line,$message,'Error');
    }

    public static function reportInfo($log,$line='',$message=''){
        Log::info($log.' '.$message.' '.$line);
        Error::saveError($log,$line,$message,'Info');
    }

    public static function reportAlert($log,$line='',$message=''){
        Log::alert($log.' '.$message.' '.$line);
        Error::saveError($log,$line,$message,'Alert');
    }

}
