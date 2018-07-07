<?php

return [

	// GENERAL
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
	'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
	'secret_pagostt_iv' => '!#$a54?3', // Secret IV de encriptación de PAgosTT, integrado con Beto
	'app_name' => env('APP_NAME', 'PagosTT'), // Nombre enviado a Cuentas365
	'app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'custom_app_keys' => ['sample'=>'c26d8c99-8836-4cd5-a850-230c9d3fbf3c'], // AppKey personalizados para ser utilizados
	'invoice_server' => 'http://www.todotix.com:7777/factura/', // Servidor donde se almacenan las facturas, pegado al Invoice ID
	'notify_email' => true, // Notificar la recepción del pago por correo electrónico
	'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
	'enable_cycle' => false, // Habilitar la facturación por ciclos
	'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
	'customer_all_payments' => true, // Habilitar si se desea aceptar pagos en masa
	'customer_recurrent_payments' => false, // Habilitar si se desea integrar a Cuentas365
	'is_cuentas365' => false, // Habilitar la plataforma es Cuentas365

];