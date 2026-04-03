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

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\Common\Arr;
use App\Helpers\Services\CacheRegenerator;
use App\Http\Requests\Admin\AddonRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AddonController extends Controller
{
	private array $data = [];
	
	public function __construct()
	{
		parent::__construct();
		
		$this->data['addons'] = [];
	}
	
	/**
	 * List all addons
	 */
	public function index()
	{
		$addons = [];
		
		try {
			
			// Load all the addons' services providers
			$addons = addon_list();
			
			// Append the Addon Options
			$addons = collect($addons)
				->map(function ($item) {
					try {
						
						$item = is_object($item) ? Arr::fromObject($item) : $item;
						
						// Append formatted name
						$name = $item['name'] ?? null;
						$displayName = $item['display_name'] ?? null;
						$item['formatted_name'] = $displayName . addon_demo_info($name);
						
						if (!empty($item['item_id'])) {
							$item['activated'] = addon_check_purchase_code($item);
						}
						
						// Append the Options
						$item['options'] = null;
						if ($item['is_compatible']) {
							$addonClass = addon_namespace($item['name'], ucfirst($item['name']));
							$item['options'] = method_exists($addonClass, 'getOptions')
								? (array)call_user_func($addonClass . '::getOptions')
								: null;
						}
						
					} catch (Throwable $e) {
						$message = $e->getMessage();
						if (!empty($message)) {
							notification($message, 'error');
						}
					}
					
					return Arr::toObject($item);
				})->toArray();
			
		} catch (Throwable $e) {
			$message = $e->getMessage();
			if (!empty($message)) {
				notification($message, 'error');
			}
		}
		
		$this->data['addons'] = $addons;
		$this->data['title'] = 'Addons';
		
		return view('admin.addons', $this->data);
	}
	
	/**
	 * Install an addon (with purchase code)
	 *
	 * @param $name
	 * @param \App\Http\Requests\Admin\AddonRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function installWithCode($name, AddonRequest $request): RedirectResponse
	{
		$addonListUrl = urlGen()->adminUrl('addons');
		
		// Get addon details
		$addon = load_addon($name);
		if (empty($addon)) {
			return redirect()->to($addonListUrl);
		}
		
		// Check if the addon is compatible with the core app
		if (!$addon->is_compatible) {
			notification($addon->compatibility_hint, 'error');
			
			return redirect()->to($addonListUrl);
		}
		
		// Install the addon
		$res = call_user_func($addon->class . '::installed');
		if (!$res) {
			$res = call_user_func($addon->class . '::install');
		}
		
		if ($res) {
			$message = trans('admin.addon_installed_successfully', ['addonName' => $addon->display_name]);
			notification($message, 'success');
			
			// Regenerate route and config cache after addon installation
			$this->regenerateCacheIfEnabled();
		} else {
			$message = trans('admin.addon_installation_failed', ['addonName' => $addon->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($addonListUrl);
	}
	
	/**
	 * Install an addon (without purchase code)
	 *
	 * @param $name
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function installWithoutCode($name): RedirectResponse
	{
		$addonListUrl = urlGen()->adminUrl('addons');
		
		// Get addon details
		$addon = load_addon($name);
		if (empty($addon)) {
			return redirect()->to($addonListUrl);
		}
		
		// Check if the addon is compatible with the core app
		if (!$addon->is_compatible) {
			notification($addon->compatibility_hint, 'error');
			
			return redirect()->to($addonListUrl);
		}
		
		// Install the addon
		$res = call_user_func($addon->class . '::install');
		
		if ($res) {
			$message = trans('admin.addon_installed_successfully', ['addonName' => $addon->display_name]);
			notification($message, 'success');
			
			// Regenerate route and config cache after addon installation
			$this->regenerateCacheIfEnabled();
		} else {
			$message = trans('admin.addon_installation_failed', ['addonName' => $addon->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($addonListUrl);
	}
	
	/**
	 * Uninstall an addon
	 *
	 * @param $name
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function uninstall($name): RedirectResponse
	{
		$addonListUrl = urlGen()->adminUrl('addons');
		
		// Get addon details
		$addon = load_addon($name);
		if (empty($addon)) {
			return redirect()->to($addonListUrl);
		}
		
		// Check if the addon is compatible with the core app
		if (!$addon->is_compatible) {
			notification($addon->compatibility_hint, 'error');
			
			return redirect()->to($addonListUrl);
		}
		
		// Uninstall the addon
		$res = call_user_func($addon->class . '::uninstall');
		
		// Result Notification
		if ($res) {
			addon_clear_uninstall($name);
			
			$message = trans('admin.addon_uninstalled_successfully', ['addonName' => $addon->display_name]);
			notification($message, 'success');
			
			// Regenerate route and config cache after addon uninstallation
			$this->regenerateCacheIfEnabled();
		} else {
			$message = trans('admin.addon_uninstallation_failed', ['addonName' => $addon->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($addonListUrl);
	}
	
	/**
	 * Delete a addon
	 *
	 * @param $addon
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($addon): RedirectResponse
	{
		$addonListUrl = urlGen()->adminUrl('addons');
		
		// ...
		// notification(trans('admin.addon_removed_successfully'), 'success');
		// notification(trans('admin.addon_removal_failed', ['addonName' => $addon]), 'error');
		
		return redirect()->to($addonListUrl);
	}
	
	/**
	 * Regenerate route and config cache if enabled in optimization settings
	 *
	 * @return void
	 */
	private function regenerateCacheIfEnabled(): void
	{
		CacheRegenerator::regenerateAllCaches();
	}
}
