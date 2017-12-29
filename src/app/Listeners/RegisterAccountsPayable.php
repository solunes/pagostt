<?php

namespace Solunes\Pagostt\App\Listeners;

class RegisterAccountsPayable {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){

            /* Crear cuentas de ventas */
            $liability_ctp = \Solunes\Pagostt\App\Account::getCode('liability_ctp')->id;
            $name = $event->name;
            $arr[] = \Pagostt::register_account($event->place_id, 'debit', $event->account_id, $event->currency_id, $event->amount, $name);
            $arr[] = \Pagostt::register_account($event->place_id, 'credit', $liability_ctp, $event->currency_id, $event->amount, $name);
            \Pagostt::register_account_array($arr);
            return $event;
    	}

    }

}
