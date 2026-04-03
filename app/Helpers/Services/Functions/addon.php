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

use App\Exceptions\Custom\CustomException;
use App\Helpers\Common\Arr;
use App\Helpers\Common\DBUtils;
use App\Helpers\Common\JsonUtils;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * @param string|null $category
 * @param bool $checkInstalled
 * @return array
 */
function addon_list(?string $category = null, bool $checkInstalled = false): array
{
	$addons = [];
	
	// Load all addons services providers
	$list = File::glob(config('larapen.core.addon.path') . '*', GLOB_ONLYDIR);
	
	if (count($list) > 0) {
		foreach ($list as $addonPath) {
			// Get addon folder name
			$addonFolderName = strtolower(last(explode(DIRECTORY_SEPARATOR, $addonPath)));
			
			// Get addon details
			$addon = load_addon($addonFolderName);
			if (empty($addon)) {
				continue;
			}
			
			// Filter for category
			if (!is_null($category) && $addon->category != $category) {
				continue;
			}
			
			// Check installed addons
			try {
				$addon->installed = ($addon->is_compatible)
					? call_user_func($addon->class . '::installed')
					: false;
			} catch (Throwable $e) {
				continue;
			}
			
			// Filter for installed addons
			if ($checkInstalled && !$addon->installed) {
				continue;
			}
			
			$addons[$addon->name] = $addon;
		}
	}
	
	return $addons;
}

/**
 * @param string|null $category
 * @return array
 */
function addon_installed_list(?string $category = null): array
{
	return addon_list($category, true);
}

/**
 * Get the addon details
 *
 * @param string|null $name
 * @return array|\stdClass|null
 */
function load_addon(?string $name)
{
	if (empty($name)) return null;
	
	try {
		// Get the addon init data
		$addonFolderPath = addon_path($name);
		$addonData = file_get_contents($addonFolderPath . '/init.json');
		$addonData = json_decode($addonData);
		
		$isCompatible = addon_check_compatibility($name);
		$compatibility = null;
		$compatibilityHint = null;
		if (!$isCompatible) {
			$compatibility = 'Not compatible';
			$compatibilityHint = addon_compatibility_hint($name);
		}
		
		// Addon details
		$addon = [
			'name'               => $addonData->name,
			'version'            => $addonData->version,
			'is_compatible'      => $isCompatible,
			'compatibility'      => $compatibility,
			'compatibility_hint' => $compatibilityHint,
			'display_name'       => $addonData->display_name,
			'description'        => $addonData->description,
			'author'             => $addonData->author,
			'category'           => $addonData->category,
			'has_installer'      => (isset($addonData->has_installer) && $addonData->has_installer == true),
			'installed'          => null,
			'activated'          => true,
			'options'            => null,
			'item_id'            => (isset($addonData->item_id)) ? $addonData->item_id : null,
			'provider'           => addon_namespace($addonData->name, ucfirst($addonData->name) . 'ServiceProvider'),
			'class'              => addon_namespace($addonData->name, ucfirst($addonData->name)),
		];
		$addon = Arr::toObject($addon);
		
	} catch (Throwable $e) {
		$addon = null;
	}
	
	return $addon;
}

/**
 * Get the addon details (Only if it's installed)
 *
 * @param string $name
 * @return array|\stdClass|null
 */
function load_installed_addon(string $name)
{
	$addon = load_addon($name);
	if (empty($addon)) {
		return null;
	}
	
	if (!$addon->is_compatible) {
		return null;
	}
	
	if (isset($addon->has_installer) && $addon->has_installer) {
		try {
			$installed = call_user_func($addon->class . '::installed');
			
			return ($installed) ? $addon : null;
		} catch (Throwable $e) {
			return null;
		}
	} else {
		return $addon;
	}
}

/**
 * @param string $addonFolderName
 * @param string|null $localNamespace
 * @return string
 */
function addon_namespace(string $addonFolderName, ?string $localNamespace = null): string
{
	if (!is_null($localNamespace)) {
		return config('larapen.core.addon.namespace') . $addonFolderName . '\\' . $localNamespace;
	} else {
		return config('larapen.core.addon.namespace') . $addonFolderName;
	}
}

/**
 * Get a file of the addon
 *
 * @param string $addonFolderName
 * @param string|null $localPath
 * @return string
 */
function addon_path(string $addonFolderName, ?string $localPath = null): string
{
	return config('larapen.core.addon.path') . $addonFolderName . '/' . $localPath;
}

/**
 * Check if a addon exists
 *
 * @param string $addonFolderName
 * @param string|null $path
 * @return bool
 */
function addon_exists(string $addonFolderName, ?string $path = null): bool
{
	$fullPath = config('larapen.core.addon.path') . $addonFolderName . '/';
	
	if (empty($path)) {
		// If the second argument is not set or is empty,
		// then, check if the addon's service provider exists instead.
		$serviceProviderFilename = ucfirst($addonFolderName) . 'ServiceProvider.php';
		$fullPath = $fullPath . $serviceProviderFilename;
	} else {
		$fullPath = $fullPath . $path;
	}
	
	return File::exists($fullPath);
}

/**
 * @param string $addonFolderName
 * @return bool
 */
function addon_installed_file_exists(string $addonFolderName): bool
{
	$addonFile = storage_path('framework/addons/' . $addonFolderName);
	
	return File::exists($addonFile);
}

/**
 * IMPORTANT: Do not change this part of the code to prevent any data-losing issue.
 *
 * @param $addon
 * @return bool
 */
function addon_check_purchase_code($addon): bool
{
	if (is_array($addon)) {
		$addon = Arr::toObject($addon);
	}
	
	$addonFile = storage_path('framework/addons/' . $addon->name);
	if (File::exists($addonFile)) {
		$purchaseCode = file_get_contents($addonFile);
		if (!empty($purchaseCode)) {
			$pattern = '#([a-z0-9]{8})-?([a-z0-9]{4})-?([a-z0-9]{4})-?([a-z0-9]{4})-?([a-z0-9]{12})#';
			$replacement = '$1-$2-$3-$4-$5';
			$purchaseCode = preg_replace($pattern, $replacement, strtolower($purchaseCode));
			if (strlen($purchaseCode) == 36) {
				$res = true;
			} else {
				$res = false;
			}
			
			return $res;
		}
	}
	
	return false;
}

/**
 * Get addons settings values (with HTML)
 *
 * @param $setting
 * @param string|null $out
 * @return mixed
 */
function addon_setting_value_html($setting, ?string $out): mixed
{
	$addons = addon_installed_list();
	if (!empty($addons)) {
		foreach ($addons as $addon) {
			$addonMethodNames = preg_grep('#^get(.+)ValueHtml$#', get_class_methods($addon->class));
			
			if (!empty($addonMethodNames)) {
				foreach ($addonMethodNames as $method) {
					try {
						$out = call_user_func($addon->class . '::' . $method, $setting, $out);
					} catch (Throwable $e) {
						continue;
					}
				}
			}
		}
	}
	
	return $out;
}

/**
 * Set addons settings values
 *
 * @param $value
 * @param $setting
 * @return bool|mixed
 */
function addon_set_setting_value($value, $setting): mixed
{
	$addons = addon_installed_list();
	if (!empty($addons)) {
		foreach ($addons as $addon) {
			
			$addonMethodNames = preg_grep('#^set(.+)Value$#', get_class_methods($addon->class));
			
			if (!empty($addonMethodNames)) {
				foreach ($addonMethodNames as $method) {
					try {
						$value = call_user_func($addon->class . '::' . $method, $value, $setting);
					} catch (Throwable $e) {
						continue;
					}
				}
			}
		}
	}
	
	return $value;
}

/**
 * Check if the addon attribute exists in the setting object
 *
 * @param $attributes
 * @param $addonAttrName
 * @return bool
 */
function addon_setting_field_exists($attributes, $addonAttrName): bool
{
	$attributes = JsonUtils::jsonToArray($attributes);
	
	if (count($attributes) > 0) {
		foreach ($attributes as $field) {
			if (isset($field['name']) && $field['name'] == $addonAttrName) {
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Create the addon attribute in the setting object
 *
 * @param $attributes
 * @param $addonAttrArray
 * @return string
 */
function addon_setting_field_create($attributes, $addonAttrArray): string
{
	$attributes = JsonUtils::jsonToArray($attributes);
	
	$attributes[] = $addonAttrArray;
	
	return JsonUtils::arrayToJson($attributes);
}

/**
 * Remove the addon attribute from the setting object
 *
 * @param $attributes
 * @param $addonAttrName
 * @return string
 */
function addon_setting_field_delete($attributes, $addonAttrName): string
{
	$attributes = JsonUtils::jsonToArray($attributes);
	
	// Get addon's Setting field array
	$addonAttrArray = Arr::where($attributes, function ($item) use ($addonAttrName) {
		return isset($item['name']) && $item['name'] == $addonAttrName;
	});
	
	// Remove the addon Setting field array
	Arr::forget($attributes, array_keys($addonAttrArray));
	
	return JsonUtils::arrayToJson($attributes);
}

/**
 * Remove the addon attribute value from the setting object values
 *
 * @param $values
 * @param $addonAttrName
 * @return array
 */
function addon_setting_value_delete($values, $addonAttrName): array
{
	$values = JsonUtils::jsonToArray($values);
	
	// Remove the addon Setting field array
	if (isset($values[$addonAttrName])) {
		unset($values[$addonAttrName]);
	}
	
	return $values;
}

/**
 * Check if a addon is compatible with the app's current version
 *
 * @param string|null $name
 * @return bool
 */
function addon_check_compatibility(?string $name): bool
{
	$currentVersion = addon_version($name);
	$minVersion = addon_minimum_version($name);
	
	$isCompatible = true;
	if (!empty($minVersion)) {
		$isCompatible = version_compare($currentVersion, $minVersion, '>=');
	}
	
	return $isCompatible;
}

/**
 * Get addon compatibility info
 *
 * @param string|null $name
 * @return string|null
 */
function addon_compatibility_hint(?string $name): ?string
{
	$minVersion = addon_minimum_version($name);
	
	$message = 'Compatible';
	if (!empty($minVersion)) {
		// $notCompatibleMessage = 'Not compatible with the app\'s current version.';
		$notCompatibleMessage = 'The app requires the addon\'s version %s or higher.';
		$notCompatibleMessage = sprintf($notCompatibleMessage, $minVersion);
		
		$isCompatible = addon_check_compatibility($name);
		$message = ($isCompatible) ? $message : $notCompatibleMessage;
	}
	
	return $message;
}

/**
 * Get a addon's current version
 *
 * @param string|null $name
 * @return string
 */
function addon_version(?string $name): string
{
	$value = null;
	
	$initFilePath = config('larapen.core.addon.path') . $name . DIRECTORY_SEPARATOR . 'init.json';
	if (file_exists($initFilePath)) {
		$buffer = file_get_contents($initFilePath);
		$array = json_decode($buffer, true);
		$value = $array['version'] ?? null;
	}
	
	return checkAndUseSemVer($value);
}

/**
 * Get addon's minimum version requirement
 *
 * @param string|null $name
 * @return string|null
 */
function addon_minimum_version(?string $name): ?string
{
	$value = null;
	
	if (!empty($name)) {
		$value = config('version.compatibility.' . $name);
		$value = is_string($value) ? $value : null;
	}
	
	return !empty($value) ? checkAndUseSemVer($value) : null;
}

/**
 * Clear the key file
 *
 * @param $name
 */
function addon_clear_uninstall($name): void
{
	$path = storage_path('framework/addons/' . strtolower($name));
	if (File::exists($path)) {
		File::delete($path);
	}
}

/**
 * @param string|null $name
 * @return string|bool|null
 */
function addon_envato_link(?string $name): bool|string|null
{
	if (empty($name)) {
		return null;
	}
	
	$addons = [
		'adyen'            => 'https://codecanyon.net/item/adyen-payment-gateway-plugin/35221465',
		'cashfree'         => 'https://codecanyon.net/item/cashfree-payment-gateway-plugin/35221544',
		'currencyexchange' => 'https://codecanyon.net/item/currency-exchange-plugin-for-laraclassified/22079713',
		'detectadsblocker' => 'https://codecanyon.net/item/detect-ads-blocker-plugin-for-laraclassified-and-jobclass/20765853',
		'domainmapping'    => 'https://codecanyon.net/item/domain-mapping-plugin-for-laraclassified-and-jobclass/22079730',
		'flutterwave'      => 'https://codecanyon.net/item/flutterwave-payment-gateway-plugin/35221451',
		'iyzico'           => 'https://codecanyon.net/item/iyzico-payment-gateway-plugin/29810094',
		'offlinepayment'   => 'https://codecanyon.net/item/offline-payment-plugin-for-laraclassified-and-jobclass/20765766',
		'paypal'           => false,
		'paystack'         => 'https://codecanyon.net/item/paystack-payment-gateway-plugin/23722624',
		'payu'             => 'https://codecanyon.net/item/payu-plugin-for-laraclassified-and-jobclass/20441945',
		'razorpay'         => 'https://codecanyon.net/item/razorpay-payment-gateway-plugin/35221560',
		'reviews'          => 'https://codecanyon.net/item/reviews-system-for-laraclassified/20441932',
		'stripe'           => 'https://codecanyon.net/item/stripe-payment-gateway-plugin-for-laraclassified-and-jobclass/19700721',
		'twocheckout'      => 'https://codecanyon.net/item/2checkout-payment-gateway-plugin-for-laraclassified-and-jobclass/19700698',
		'watermark'        => 'https://codecanyon.net/item/watermark-plugin-for-laraclassified/19700729',
	];
	
	return $addons[$name] ?? null;
}

/**
 * @param string|null $name
 * @return string|null
 */
function addon_demo_info(?string $name): ?string
{
	if (!isDemoEnv() && !isDevEnv()) {
		return null;
	}
	
	if (empty($name)) {
		return null;
	}
	
	$purchaseLink = addon_envato_link($name);
	
	$out = ' ';
	if ($purchaseLink === false) {
		$info = 'This addon is free and comes with the app.';
		$info = ' data-bs-toggle="tooltip" title="' . $info . '"';
		$out .= '<span class="badge bg-success-subtle text-success-emphasis fw-normal"' . $info . '>Free</span>';
	} else {
		if (!empty($purchaseLink)) {
			$info = ' data-bs-toggle="tooltip" title="Purchase It"';
			$link = ' ';
			$link .= '<a href="' . $purchaseLink . '" target="_blank"' . $info . '>';
			$link .= '<i class="bi bi-box-arrow-up-right"></i>';
			$link .= '</a>';
			
			$info = 'This addon is optional, and is sold separately.';
			$info = ' data-bs-toggle="tooltip" title="' . $info . '"';
			$out .= '<span class="badge bg-warning-subtle text-warning-emphasis fw-normal"' . $info . '>Sold as an extra</span>' . $link;
		} else {
			$info = 'This addon is optional, and does not come with the app.';
			$info = ' data-bs-toggle="tooltip" title="' . $info . '"';
			$out .= '<span class="badge bg-info-subtle text-info-emphasis fw-normal"' . $info . '>Not included</span>';
		}
	}
	
	return $out;
}

/**
 * Get the next setting's position
 *
 * @param string|null $orderBy
 * @return int
 */
function getNextSettingPosition(?string $orderBy = 'id'): int
{
	$orderBy = !empty($orderBy) ? $orderBy : 'id';
	
	$lft = 2;
	try {
		$latestSetting = Setting::query()->orderByDesc($orderBy)->first();
		if (!empty($latestSetting)) {
			$lft = (int)$latestSetting->lft + 2;
		}
	} catch (Throwable $e) {
	}
	
	return $lft;
}

/**
 * Create addon setting
 *
 * @param array $addonSetting
 * @return bool
 * @throws \App\Exceptions\Custom\CustomException
 */
function create_addon_setting(array $addonSetting): bool
{
	if (empty($addonSetting['name']) || empty($addonSetting['label'])) {
		$message = 'The columns "name" and "label" are required';
		throw new CustomException($message);
	}
	
	// Remove the addon setting (for security)
	drop_addon_setting($addonSetting['name']);
	
	// Get the setting's position
	$lft = getNextSettingPosition();
	$rgt = $lft + 1;
	
	$addonSetting['description'] = $addonSetting['description'] ?? $addonSetting['label'];
	$addonSetting['fields'] = null;
	$addonSetting['field_values'] = null;
	$addonSetting['parent_id'] = 0;
	$addonSetting['lft'] = $lft;
	$addonSetting['rgt'] = $rgt;
	$addonSetting['depth'] = 0;
	$addonSetting['active'] = 1;
	
	// Create addon setting
	DB::statement('ALTER TABLE ' . DBUtils::table((new Setting())->getTable()) . ' AUTO_INCREMENT = 1;');
	$setting = Setting::create($addonSetting);
	
	return !empty($setting);
}

/**
 * Remove the addon setting
 *
 * @param string $name
 * @return void
 */
function drop_addon_setting(string $name): void
{
	// Remove the addon setting
	$setting = Setting::where('name', $name)->first();
	if (!empty($setting)) {
		$setting->delete();
	}
}
