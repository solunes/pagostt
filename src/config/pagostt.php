<?php

return [

	// GENERAL
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS'; // Llave de encriptación, reemplazar por la del proyecto
	'app_name' => env('APP_NAME', 'PagosTT'), // Nombre enviado a Cuentas365
	'app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'customer_recurrent_payments' => false, // Habilitar si se desea integrar a Cuentas365

];