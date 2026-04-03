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

namespace App\Http\Requests\Admin;

use App\Rules\PurchaseCodeRule;
use stdClass;

class AddonRequest extends Request
{
	protected array|stdClass|null $addon = null;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		$this->addon = load_addon($this->segment(3));
		if (!empty($this->addon)) {
			$addonId = data_get($this->addon, 'item_id');
			if (!empty($addonId)) {
				$rules['purchase_code'] = ['required', new PurchaseCodeRule($addonId)];
			}
		}
		
		return $rules;
	}
	
	/**
	 * Handle a passed validation attempt.
	 *
	 * @return void
	 */
	protected function passedValidation(): void
	{
		if (empty($this->addon)) return;
		
		$addonName = data_get($this->addon, 'name');
		$purchaseCode = $this->input('purchase_code');
		
		if (empty($addonName)) return;
		
		$addonFile = storage_path('framework/addons/' . $addonName);
		file_put_contents($addonFile, $purchaseCode);
	}
}
