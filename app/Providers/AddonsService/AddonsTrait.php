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

namespace App\Providers\AddonsService;

use App\Helpers\Common\Arr;

trait AddonsTrait
{
	/**
	 * Load all the installed addons
	 *
	 * @return void
	 */
	private function loadAddons(): void
	{
		$addons = addon_installed_list();
		$addons = collect($addons)
			->map(function ($item) {
				if (is_object($item)) {
					$item = Arr::fromObject($item);
				}
				if (!empty($item['item_id'])) {
					$item['installed'] = addon_check_purchase_code($item);
				}
				
				return $item;
			})->toArray();
		
		config()->set('addons', $addons);
		config()->set('addons.installed', collect($addons)->whereStrict('installed', true)->toArray());
	}
}
