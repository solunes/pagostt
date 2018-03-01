<?php

namespace Solunes\Pagostt\App\Listeners;

class CustomerCreating {

    public function handle($event) {
        $event->full_name = $event->first_name.' '.$event->last_name;
        if(!$event->member_code){
            $event->member_code = rand(10000,99999);
        }
        $user = \App\User::where('email',$event->email)->orWhere('cellphone',$event->phone)->orWhere('username',$event->ci_number)->first();
        if(!$user){
            $user = new \App\User;
            $user->name = $event->full_name;
            $user->email = $event->email;
            $user->cellphone = $event->phone;
            $user->username = $event->ci_number;
            $user->password = $event->member_code;
            $user->save();
            $user->role_user()->attach(2); // Agregar como miembro
        } else {
            $save = false;
            if(!$user->email&&!\App\User::where('email', $event->email)->first()){
                $user->email = $event->email;
                $save = true;
            }
            if(!$user->cellphone&&!\App\User::where('cellphone', $event->phone)->first()){
                $user->cellphone = $event->phone;
                $save = true;
            }
            if(!$user->username&&!\App\User::where('username', $event->ci_number)->first()){
                $user->username = $event->ci_number;
                $save = true;
            }
            if($save){
                $user->save();
            }
        }
        $event->user_id = $user->id;

        // Enviar a Cuentas365
        if(config('pagostt.is_cuentas365')===false){
            \Pagostt::sendCustomerTo('http://cuentas365.test', $event);
        }
        return $event;
    }

}