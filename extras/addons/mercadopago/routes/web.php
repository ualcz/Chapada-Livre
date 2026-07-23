<?php

use extras\addons\mercadopago\app\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
	Route::post('mercadopago/webhook', [WebhookController::class, 'handleNotification'])
		->name('mercadopago.webhook');
	Route::get('mercadopago/webhook', [WebhookController::class, 'handleNotification']);
});
