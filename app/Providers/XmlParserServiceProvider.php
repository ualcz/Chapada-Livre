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

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Common\XmlParser;

class XmlParserServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->singleton(XmlParser::class, function ($app) {
			return new XmlParser([
				'throw_on_error'      => config('xmlparser.throw_on_error', false),
				'preserve_whitespace' => config('xmlparser.preserve_whitespace', false),
				'validate'            => config('xmlparser.validate', false),
			]);
		});
	}
	
	public function boot(): void
	{
		$this->publishes([
			__DIR__ . '/../../config/xmlparser.php' => config_path('xmlparser.php'),
		], 'config');
	}
}
