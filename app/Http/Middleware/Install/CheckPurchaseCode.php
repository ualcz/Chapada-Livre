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

namespace App\Http\Middleware\Install;

use App\Exceptions\Custom\InvalidPurchaseCodeException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

trait CheckPurchaseCode
{
	/**
	 * Check Purchase Code
	 * ===================
	 * Checking your purchase code. If you do not have one, please follow this link:
	 * https://codecanyon.net/item/laraclassified-geo-classified-ads-cms/16458425
	 * to acquire a valid code.
	 *
	 * IMPORTANT: Do not change this part of the code to prevent any data losing issue.
	 *
	 * @return void
	 * @throws \App\Exceptions\Custom\InvalidPurchaseCodeException
	 */
	protected function checkPurchaseCode(): void
	{
		return;
	}

	// PRIVATE

	/**
	 * Check if the purchase code verification is required
	 * Make the purchase code verification only if 'installed' file exists
	 *
	 * @return bool
	 */
	private function isPurchaseCodeVerificationRequired(): bool
	{
		return false;
	}

	/**
	 * Don't check the purchase code for these areas (install, admin, etc.)
	 *
	 * @return bool
	 */
	private function isCurrentUriExemptFromPurchaseCodeVerification(): bool
	{
		$exemptArray = ['install', urlGen()->adminUri()];

		return in_array(request()->segment(1), $exemptArray);
	}
}
