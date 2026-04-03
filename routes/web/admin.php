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

use App\Http\Controllers\Web\Admin\Action\ConfigCacheController;
use App\Http\Controllers\Web\Admin\Action\Heavy\ClearCache;
use App\Http\Controllers\Web\Admin\Action\Heavy\ClearImageThumbs;
use App\Http\Controllers\Web\Admin\Action\RouteCacheController;
use App\Http\Controllers\Web\Admin\ActionController;
use App\Http\Controllers\Web\Admin\AddonController;
use App\Http\Controllers\Web\Admin\AdvertisingController;
use App\Http\Controllers\Web\Admin\BackupController;
use App\Http\Controllers\Web\Admin\BlacklistController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\CategoryFieldController;
use App\Http\Controllers\Web\Admin\CityController;
use App\Http\Controllers\Web\Admin\CountryController;
use App\Http\Controllers\Web\Admin\CurrencyController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\FieldController;
use App\Http\Controllers\Web\Admin\FieldOptionController;
use App\Http\Controllers\Web\Admin\InlineRequestController;
use App\Http\Controllers\Web\Admin\LanguageController;
use App\Http\Controllers\Web\Admin\MenuController;
use App\Http\Controllers\Web\Admin\MenuItemController;
use App\Http\Controllers\Web\Admin\MetaTagController;
use App\Http\Controllers\Web\Admin\PackageController;
use App\Http\Controllers\Web\Admin\PageController;
use App\Http\Controllers\Web\Admin\Panel\Library\PanelRoutes;
use App\Http\Controllers\Web\Admin\PaymentController;
use App\Http\Controllers\Web\Admin\PaymentMethodController;
use App\Http\Controllers\Web\Admin\PermissionController;
use App\Http\Controllers\Web\Admin\PictureController;
use App\Http\Controllers\Web\Admin\PostController;
use App\Http\Controllers\Web\Admin\ReportTypeController;
use App\Http\Controllers\Web\Admin\RoleController;
use App\Http\Controllers\Web\Admin\SectionController;
use App\Http\Controllers\Web\Admin\SectionPresetsController;
use App\Http\Controllers\Web\Admin\SettingController;
use App\Http\Controllers\Web\Admin\SubAdmin1Controller;
use App\Http\Controllers\Web\Admin\SubAdmin2Controller;
use App\Http\Controllers\Web\Admin\SystemInfoController;
use App\Http\Controllers\Web\Admin\SystemPhpInfoController;
use App\Http\Controllers\Web\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin Panel Area
Route::middleware(['admin', 'clearance', 'banned.user', 'no.http.cache'])
	->group(function ($router) {
		// Dashboard
		Route::controller(DashboardController::class)
			->group(function ($router) {
				Route::get('dashboard', 'dashboard')->name('admin.dashboard');
				Route::get('/', 'redirect')->name('admin.panel');
			});
		
		// Extra (must be called before CRUD)
		Route::controller(SettingController::class)
			->group(function ($router) {
				Route::get('settings/find/{name}', 'find')->where('name', '[^/]+');
				Route::get('settings/reset/{name}', 'reset')->where('name', '[^/]+');
			});
		Route::controller(SectionController::class)
			->group(function ($router) {
				Route::get('sections/find/{name}', 'find')->where('name', '[^/]+');
				Route::get('sections/reset/all/{action}', 'resetAll')->where('action', 'reorder|options');
			});
		Route::controller(LanguageController::class)
			->group(function ($router) {
				Route::get('languages/sync-files', 'syncFilesLines');
				Route::get('languages/texts/{lang?}/{file?}', 'showTexts')
					->where('lang', '[^/]*')
					->where('file', '[^/]*');
				Route::post('languages/texts/{lang}/{file}', 'updateTexts')
					->where('lang', '[^/]+')
					->where('file', '[^/]+');
			});
		Route::get('permissions/seed-predefined-permissions', [PermissionController::class, 'seedPredefinedPermissions']);
		Route::get('blacklists/add', [BlacklistController::class, 'banUser']);
		Route::get('categories/rebuild-nested-set-nodes', [CategoryController::class, 'rebuildNestedSetNodes']);
		Route::get('menus/reset', [MenuController::class, 'reset']);
		Route::get('menus/{menuId}/menu-items/reset', [MenuItemController::class, 'reset']);
		Route::get('menus/{menuId}/menu-items/rebuild-nested-set-nodes', [MenuItemController::class, 'rebuildNestedSetNodes']);
		
		// Panel's Default Routes
		// ---
		// menus
		PanelRoutes::resource('menus', MenuController::class);
		PanelRoutes::resource('menus/{menuId}/menu-items', MenuItemController::class);
		PanelRoutes::resource('menus/{menuId}/menu-items/{parentIdentifier}/submenu-items', MenuItemController::class);
		PanelRoutes::resource('menu-items/{parentIdentifier}/submenu-items', MenuItemController::class); // Rewrite to valid route
		PanelRoutes::resource('menu-items', MenuItemController::class);                                  // Redirect to /menus
		
		// categories
		PanelRoutes::resource('categories', CategoryController::class);
		PanelRoutes::resource('categories/{parentIdentifier}/subcategories', CategoryController::class);
		PanelRoutes::resource('categories/{categoryId}/custom-fields', CategoryFieldController::class);
		PanelRoutes::resource('custom-fields', FieldController::class);
		PanelRoutes::resource('custom-fields/{fieldId}/options', FieldOptionController::class);
		PanelRoutes::resource('custom-fields/{fieldId}/categories', CategoryFieldController::class);
		
		// countries
		PanelRoutes::resource('countries', CountryController::class);
		
		// admins1
		PanelRoutes::resource('countries/{countryCode}/admins1', SubAdmin1Controller::class);
		PanelRoutes::resource('admins1', SubAdmin1Controller::class); // Redirect to /countries
		
		// admins2
		PanelRoutes::resource('countries/{countryCode}/admins1/{admin1Code}/admins2', SubAdmin2Controller::class);
		PanelRoutes::resource('countries/{countryCode}/admins2', SubAdmin2Controller::class);
		PanelRoutes::resource('admins1/{admin1Code}/admins2', SubAdmin2Controller::class); // Rewrite to valid route
		PanelRoutes::resource('admins2', SubAdmin2Controller::class);                      // Redirect to /countries
		
		// cities
		PanelRoutes::resource('countries/{countryCode}/admins1/{admin1Code}/admins2/{admin2Code}/cities', CityController::class);
		PanelRoutes::resource('countries/{countryCode}/admins1/{admin1Code}/cities', CityController::class);
		PanelRoutes::resource('countries/{countryCode}/admins2/{admin2Code}/cities', CityController::class);
		PanelRoutes::resource('countries/{countryCode}/cities', CityController::class);
		PanelRoutes::resource('admins1/{admin1Code}/admins2/{admin2Code}/cities', CityController::class); // Rewrite to valid route
		PanelRoutes::resource('admins1/{admin1Code}/cities', CityController::class);                      // Rewrite to valid route
		PanelRoutes::resource('admins2/{admin2Code}/cities', CityController::class);                      // Rewrite to valid route
		PanelRoutes::resource('cities', CityController::class);                                           // Redirect to /countries
		
		PanelRoutes::resource('advertisings', AdvertisingController::class);
		PanelRoutes::resource('blacklists', BlacklistController::class);
		PanelRoutes::resource('currencies', CurrencyController::class);
		PanelRoutes::resource('homepage/sections', SectionController::class);
		PanelRoutes::resource('languages', LanguageController::class);
		PanelRoutes::resource('meta-tags', MetaTagController::class);
		PanelRoutes::resource('packages/promotion', PackageController::class);
		PanelRoutes::resource('packages/subscription', PackageController::class);
		PanelRoutes::resource('pages', PageController::class);
		PanelRoutes::resource('payments/promotion', PaymentController::class);
		PanelRoutes::resource('payments/subscription', PaymentController::class);
		PanelRoutes::resource('payment-methods', PaymentMethodController::class);
		PanelRoutes::resource('permissions', PermissionController::class);
		PanelRoutes::resource('pictures', PictureController::class);
		PanelRoutes::resource('posts', PostController::class);
		PanelRoutes::resource('report-types', ReportTypeController::class);
		PanelRoutes::resource('roles', RoleController::class);
		PanelRoutes::resource('settings', SettingController::class);
		PanelRoutes::resource('users', UserController::class);
		
		// Others
		Route::get('account', [UserController::class, 'account']);
		Route::post('ajax/{table}/{field}', [InlineRequestController::class, 'make'])
			->where('table', '[^/]+')
			->where('field', '[^/]+');
		
		// Backup
		Route::controller(BackupController::class)
			->group(function ($router) {
				Route::get('backups', 'index');
				Route::put('backups/create', 'create');
				Route::get('backups/download', 'download');
				Route::delete('backups/delete', 'delete');
			});
		
		// Actions
		Route::prefix('actions')
			->group(function () {
				Route::controller(ActionController::class)
					->group(function ($router) {
						Route::get('update-database-charset-collation', 'updateDbConnectionCharsetAndCollation');
						Route::get('clear-cached-assets', 'clearCachedAssets');
						Route::post('maintenance/{mode}', 'maintenance')->where('mode', 'down');
						Route::get('maintenance/{mode}', 'maintenance')->where('mode', 'up');
					});
				
				// Route Cache Actions
				Route::controller(RouteCacheController::class)
					->prefix('route-cache')
					->group(function ($router) {
						Route::get('regenerate', 'regenerate');
						Route::get('generate', 'generate');
						Route::get('clear', 'clear');
					});
				
				// Config Cache Actions
				Route::controller(ConfigCacheController::class)
					->prefix('config-cache')
					->group(function ($router) {
						Route::get('regenerate', 'regenerate');
						Route::get('generate', 'generate');
						Route::get('clear', 'clear');
					});
				
				// Heavy Operations (Background Jobs)
				Route::prefix('heavy')
					->group(function () {
						// Clear Cache
						Route::controller(ClearCache::class)
							->prefix('clear-cache')
							->group(function () {
								Route::get('/', 'index')->name('admin.heavy.clear-cache.index');
								Route::get('status', 'status')->name('admin.heavy.clear-cache.status');
							});
						
						// Clear Image Thumbnails
						Route::controller(ClearImageThumbs::class)
							->prefix('clear-image-thumbs')
							->group(function () {
								Route::get('/', 'index')->name('admin.heavy.clear-image-thumbs.index');
								Route::get('status', 'status')->name('admin.heavy.clear-image-thumbs.status');
							});
					});
			});
		
		// Addons
		Route::controller(AddonController::class)
			->group(function ($router) {
				$router->pattern('addon', '.+');
				Route::get('addons', 'index');
				Route::post('addons/{addon}/install/code', 'installWithCode');
				Route::get('addons/{addon}/install', 'installWithoutCode');
				Route::get('addons/{addon}/uninstall', 'uninstall');
				Route::get('addons/{addon}/delete', 'delete');
			});
		
		// Homepage Presets
		Route::controller(SectionPresetsController::class)
			->group(function ($router) {
				$router->pattern('index', '[0-9]+');
				Route::get('homepage/presets', 'showPresetList');
				Route::post('homepage/presets/{index}', 'applyPreset');
			});
		
		// System Info
		Route::get('system', [SystemInfoController::class, 'index'])->name('admin.system');
		Route::get('system/phpinfo', [SystemPhpInfoController::class, 'index'])->name('admin.system.phpinfo');
		Route::get('system/phpinfo/raw', [SystemPhpInfoController::class, 'rawVersion'])
			->name('admin.system.phpinfo.raw');
	});
