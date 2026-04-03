<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

return [
	// PHP minimum version required
	'php' => '8.2',
	
	// Latest app's version
	'app' => '18.2.0',
	
	// Current app's version (i.e., App's version in the .env file)
	'env' => function_exists('env') ? env('APP_VERSION') : null,
	
	// Addons minimum version required
	'compatibility' => [
		'adyen'            => '2.2.4',
		'cashfree'         => '2.2.5',
		'currencyexchange' => '4.3.6',
		'detectadsblocker' => '2.0.4',
		'domainmapping'    => '5.5.0',
		'flutterwave'      => '2.2.4',
		'iyzico'           => '2.2.6',
		'offlinepayment'   => '4.1.6',
		'paystack'         => '2.2.4',
		'payu'             => '3.2.4',
		'razorpay'         => '2.2.4',
		'reviews'          => '4.4.5',
		'stripe'           => '3.2.5',
		'twocheckout'      => '3.2.6',
		'watermark'        => '3.2.0',
	],
];
