<?php

use extras\addons\mercadopago\app\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
	Route::post('mercadopago/webhook', [WebhookController::class, 'handleNotification'])
		->name('mercadopago.webhook');
	Route::get('mercadopago/webhook', [WebhookController::class, 'handleNotification']);
	Route::get('mercadopago/check-status/{paymentId}', [WebhookController::class, 'checkStatus'])
		->name('mercadopago.check-status');
});

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
	Route::post('mercadopago/webhook', [WebhookController::class, 'handleNotification']);
	Route::get('mercadopago/webhook', [WebhookController::class, 'handleNotification']);
	Route::get('mercadopago/check-status/{paymentId}', [WebhookController::class, 'checkStatus']);
});
