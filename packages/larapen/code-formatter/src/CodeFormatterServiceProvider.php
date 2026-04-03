<?php

namespace Larapen\CodeFormatter;

use Illuminate\Support\ServiceProvider;

class CodeFormatterServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->singleton('code.formatter', function () {
			return new CodeFormatter();
		});
	}
	
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides(): array
	{
		return ['code.formatter'];
	}
}
