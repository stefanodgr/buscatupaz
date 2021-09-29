<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\User;

class AdminZoomUser extends Controller
{
    //FUNCION CREADA PARA CREAR TODOS LOS USARIOS DEL ROL TERAPEUTA ASIGNARLO A LA CUENTA DE ZOOM
    public function CreateUserZoom(){




       $users       = Role::where('display_name', 'Teachers')->first()->users()->where('last_login','>','2020-09-01')->get();
      foreach($users as $users ){
        $id             = $user->id;
        $first_name     = $user->first_name;
        $last_name      = $user->last_name;
        $email          = $user->email;

        
       $meeting = [];
       $meeting['id']           = $id;
       $meeting['first_name']   = $first_name;
       $meeting['last_name']    = $last_name;
       $meeting['email']        = $email;
       $meeting['type']         = 'basic';
       return self::sendRequest('users/'.$class->teacher->email.'/meetings', $meeting,"POST");
      }


    }



}
