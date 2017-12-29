<?php

app('api.router')->group(['version'=>'v1', 'namespace'=>'Solunes\\Pagostt\\App\\Http\\Controllers\\Api'], function($api){
	$api->get('pagos-de-cliente/{app_token}/{customer_id}', 'PagosttController@getCustomerPayments');
	$api->get('pago-confirmado/{payment_code}', 'PagosttController@getSuccessfulPayment');
});