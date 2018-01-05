<?php

namespace Solunes\Pagostt\App\Listeners;

class CustomerCreating {

    public function handle($event) {
        $event->full_name = $event->first_name.' '.$event->last_name;
        if(!$event->member_code){
            $event->member_code = rand(10000,99999);
        }
        $user = \App\User::where('email',$event->email)->where('cellphone',$event->phone)->first();
        if(!$user){
            $user = new \App\User;
            $user->name = $event->full_name;
            $user->email = $event->email;
            $user->cellphone = $event->phone;
            $user->username = $event->ci_number;
            $user->password = $event->member_code;
            $user->save();
            $user->role_user()->attach(2); // Agregar como miembro
        }
        $event->user_id = $user->id;

        // Enviar a Cuentas365
        if(!config('pagostt.is_cuentas365')){
            \Pagostt::sendCustomerTo('http://cuentas365.test', $event);
        }
        return $event;
    }

}