<?php

namespace Tests\Feature;

use extras\addons\mercadopago\app\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MercadopagoWebhookTest extends TestCase
{
	public function test_merchant_order_webhook_queries_payment_details_when_payment_is_not_yet_approved(): void
	{
		Http::fake([
			'https://api.mercadopago.com/merchant_orders/123' => Http::response([
				'status' => 'closed',
				'payments' => [
					['id' => 456, 'status' => 'pending'],
				],
			], 200),
			'https://api.mercadopago.com/v1/payments/456' => Http::response([
				'status' => 'pending',
			], 200),
		]);

		$request = Request::create('/mercadopago/webhook', 'POST', [
			'type' => 'topic_merchant_order_wh',
			'id'   => '123',
		]);

		$response = (new WebhookController())->handleNotification($request);

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame('pending', $response->getData(true)['status']);
		Http::assertSentCount(2);
		Http::assertSent(function ($request) {
			return $request->url() === 'https://api.mercadopago.com/v1/payments/456';
		});
	}
}
