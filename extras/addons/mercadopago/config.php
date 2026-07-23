<?php

return [
	'mercadopago' => [
		'mode'           => env('MERCADOPAGO_MODE', 'sandbox'),
		'accessToken'    => env('MERCADOPAGO_ACCESS_TOKEN', ''),
		'publicKey'      => env('MERCADOPAGO_PUBLIC_KEY', ''),
		'referrersHosts' => ['mercadopago.com', 'mercadopago.com.br'],
	],
];
