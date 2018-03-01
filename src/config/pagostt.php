<?php

return [

	// GENERAL
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
	'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
	'app_name' => env('APP_NAME', 'PagosTT'), // Nombre enviado a Cuentas365
	'app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'invoice_server' => 'http://www.todotix.com:7777/factura/', // Servidor donde se almacenan las facturas, pegado al Invoice ID
	'notify_email' => true, // Notificar la recepción del pago por correo electrónico
	'customer_recurrent_payments' => false, // Habilitar si se desea integrar a Cuentas365
	'is_cuentas365' => false, // Habilitar la plataforma es Cuentas365

];