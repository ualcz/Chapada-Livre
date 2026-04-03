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

namespace App\Models\Setting;

use App\Http\Controllers\Web\Admin\SettingController;

/*
 * settings.optimization.option
 */

class OptimizationSetting extends BaseSetting
{
	private static array $currentValues = [];
	
	public static function getFieldValues($value, $disk)
	{
		$value = is_array($value) ? $value : [];
		
		$cacheDrivers = (array)config('larapen.options.cache');
		$defaultCacheDriver = 'file';
		$disabledCacheDriver = 'array';
		
		$defaultValue = [
			'cache_driver'             => $defaultCacheDriver,
			'cache_expiration'         => '86400',
			'memcached_servers_1_host' => '127.0.0.1',
			'memcached_servers_1_port' => '11211',
			'redis_client'             => 'predis',
			'redis_cluster'            => 'predis',
			'redis_host'               => '127.0.0.1',
			'redis_password'           => null,
			'redis_port'               => '6379',
			'redis_database'           => '0',
			'queue_driver'             => 'sync',
			'lazy_loading_activation'  => '0',
			'minify_html_activation'   => '0',
			'route_cache_enabled'      => '0',
			'config_cache_enabled'     => '0',
		];
		
		$value = array_merge($defaultValue, $value);
		
		// Store current values for button display logic
		self::$currentValues = $value;
		
		// During the Cache variable updating from the Admin panel,
		// Check if the /.env file's cache configuration variables are different to the DB value,
		// If so, then display the right value from the /.env file.
		if (routeActionHas(SettingController::class . '@edit')) {
			// Cache
			if (array_key_exists('cache_driver', $value) && getenv('CACHE_STORE')) {
				if ($value['cache_driver'] != env('CACHE_STORE')) {
					$value['cache_driver'] = env('CACHE_STORE');
				}
			}
			if (array_key_exists('memcached_servers_1_host', $value) && getenv('MEMCACHED_SERVER_1_HOST')) {
				if ($value['memcached_servers_1_host'] != env('MEMCACHED_SERVER_1_HOST')) {
					$value['memcached_servers_1_host'] = env('MEMCACHED_SERVER_1_HOST');
				}
			}
			if (array_key_exists('memcached_servers_1_port', $value) && getenv('MEMCACHED_SERVER_1_PORT')) {
				if ($value['memcached_servers_1_port'] != env('MEMCACHED_SERVER_1_PORT')) {
					$value['memcached_servers_1_port'] = env('MEMCACHED_SERVER_1_PORT');
				}
			}
			if (array_key_exists('redis_client', $value) && getenv('REDIS_CLIENT')) {
				if ($value['redis_client'] != env('REDIS_CLIENT')) {
					$value['redis_client'] = env('REDIS_CLIENT');
				}
			}
			if (array_key_exists('redis_cluster', $value) && getenv('REDIS_CLUSTER')) {
				if ($value['redis_cluster'] != env('REDIS_CLUSTER')) {
					$value['redis_cluster'] = env('REDIS_CLUSTER');
				}
			}
			if (array_key_exists('redis_host', $value) && getenv('REDIS_HOST')) {
				if ($value['redis_host'] != env('REDIS_HOST')) {
					$value['redis_host'] = env('REDIS_HOST');
				}
			}
			if (array_key_exists('redis_password', $value) && getenv('REDIS_PASSWORD')) {
				if ($value['redis_password'] != env('REDIS_PASSWORD')) {
					$value['redis_password'] = env('REDIS_PASSWORD');
				}
			}
			if (array_key_exists('redis_port', $value) && getenv('REDIS_PORT')) {
				if ($value['redis_port'] != env('REDIS_PORT')) {
					$value['redis_port'] = env('REDIS_PORT');
				}
			}
			if (array_key_exists('redis_database', $value) && getenv('REDIS_DB')) {
				if ($value['redis_database'] != env('REDIS_DB')) {
					$value['redis_database'] = env('REDIS_DB');
				}
			}
			
			// Queue
			if (array_key_exists('queue_driver', $value) && getenv('QUEUE_CONNECTION')) {
				if ($value['queue_driver'] != env('QUEUE_CONNECTION')) {
					$value['queue_driver'] = env('QUEUE_CONNECTION');
				}
			}
		}
		
		// If a non-supported cache driver is filled, use the default fallback
		$cacheDriver = $value['cache_driver'] ?? null;
		if (!empty($cacheDriver)) {
			// If a non-supported cache driver is filled, use the default fallback
			if (!array_key_exists($cacheDriver, $cacheDrivers)) {
				$value['cache_driver'] = $defaultCacheDriver;
			}
		} else {
			// If the cache drive is empty, use the cache disabling driver
			$value['cache_driver'] = $disabledCacheDriver;
		}
		
		return $value;
	}
	
	public static function setFieldValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		$cacheDrivers = (array)config('larapen.options.cache');
		$queueDrivers = (array)config('larapen.options.queue');
		$queue = 'mail,sms,thumbs,default';
		
		$fields = [];
		
		$tabName = trans('admin.caching_system_title');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'caching_system_sep',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'    => 'cache_driver',
			'label'   => trans('admin.cache_driver_label'),
			'type'    => 'select2_from_array',
			'options' => $cacheDrivers,
			'hint'    => trans('admin.cache_driver_hint'),
			'wrapper' => [
				'class' => 'col-md-6',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'cache_expiration',
			'label'   => trans('admin.cache_expiration_label'),
			'type'    => 'number',
			'hint'    => trans('admin.cache_expiration_hint'),
			'wrapper' => [
				'class' => 'col-md-6 cache-enabled',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'  => 'cache_driver_info_sep',
			'type'  => 'custom_html',
			'value' => trans('admin.card_light', ['content' => trans('admin.cache_driver_info')]),
			'tab'   => $tabName,
		];
		
		$fields[] = [
			'name'    => 'file_cache_driver_notice',
			'type'    => 'custom_html',
			'value'   => trans('admin.card_light_warning', [
				'content' => trans('admin.file_cache_driver_notice', ['app_path' => base_path()]),
			]),
			'wrapper' => [
				'class' => 'col-md-12 file',
			],
			'tab'     => $tabName,
		];
		
		$fields[] = [
			'name'    => 'memcached_sep',
			'type'    => 'custom_html',
			'value'   => trans('admin.memcached_sep_value'),
			'wrapper' => [
				'class' => 'col-md-12 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_persistent_id',
			'label'   => trans('admin.memcached_persistent_id_label'),
			'type'    => 'text',
			'hint'    => trans('admin.memcached_persistent_id_hint'),
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'separator_clear_1',
			'type'    => 'custom_html',
			'value'   => '<div style="clear: both;"></div>',
			'wrapper' => [
				'class' => 'col-md-12 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_sasl_username',
			'label'   => trans('admin.memcached_sasl_username_label'),
			'type'    => 'text',
			'hint'    => trans('admin.memcached_sasl_username_hint'),
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_sasl_password',
			'label'   => trans('admin.memcached_sasl_password_label'),
			'type'    => 'text',
			'hint'    => trans('admin.memcached_sasl_password_hint'),
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_sep',
			'type'    => 'custom_html',
			'value'   => trans('admin.memcached_servers_sep_value'),
			'wrapper' => [
				'class' => 'col-md-12 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_1_host',
			'label'   => trans('admin.memcached_servers_host_label', ['num' => 1]),
			'type'    => 'text',
			'hint'    => trans('admin.memcached_servers_host_hint'),
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_1_port',
			'label'   => trans('admin.memcached_servers_port_label', ['num' => 1]),
			'type'    => 'number',
			'hint'    => trans('admin.memcached_servers_port_hint'),
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_2_host',
			'label'   => trans('admin.memcached_servers_host_label', ['num' => 2]) . ' (' . trans('admin.Optional') . ')',
			'type'    => 'text',
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_2_port',
			'label'   => trans('admin.memcached_servers_port_label', ['num' => 2]) . ' (' . trans('admin.Optional') . ')',
			'type'    => 'number',
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_3_host',
			'label'   => trans('admin.memcached_servers_host_label', ['num' => 3]) . ' (' . trans('admin.Optional') . ')',
			'type'    => 'text',
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'memcached_servers_3_port',
			'label'   => trans('admin.memcached_servers_port_label', ['num' => 3]) . ' (' . trans('admin.Optional') . ')',
			'type'    => 'number',
			'wrapper' => [
				'class' => 'col-md-6 memcached',
			],
			'tab'     => $tabName,
		];
		
		$tabName = trans('admin.queue_title');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'queue_title',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'    => 'queue_driver',
			'label'   => trans('admin.queue_driver_label'),
			'type'    => 'select2_from_array',
			'options' => $queueDrivers,
			'hint'    => trans('admin.queue_driver_hint'),
			'wrapper' => [
				'class' => 'col-md-6',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'queue_driver_info',
			'type'    => 'custom_html',
			'value'   => trans('admin.card_light_warning', [
				'content' => trans('admin.queue_driver_info', [
					'cmd' => getRightPathsForCmd('php artisan queue:work --queue=mail,sms,thumbs,default', wrapped: false),
				]),
			]),
			'wrapper' => [
				'class' => 'col-md-12',
			],
			'tab'     => $tabName,
		];
		
		$fields[] = [
			'name'    => 'sqs_title',
			'type'    => 'custom_html',
			'value'   => trans('admin.sqs_title'),
			'wrapper' => [
				'class' => 'col-md-12 sqs',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'     => 'sqs_key',
			'label'    => trans('admin.sqs_key_label'),
			'type'     => 'text',
			'required' => true,
			'hint'     => trans('admin.sqs_key_hint'),
			'wrapper'  => [
				'class' => 'col-md-12 sqs',
			],
			'tab'      => $tabName,
		];
		$fields[] = [
			'name'     => 'sqs_secret',
			'label'    => trans('admin.sqs_secret_label'),
			'type'     => 'text',
			'required' => true,
			'hint'     => trans('admin.sqs_secret_hint'),
			'wrapper'  => [
				'class' => 'col-md-12 sqs',
			],
			'tab'      => $tabName,
		];
		$fields[] = [
			'name'    => 'sqs_prefix',
			'label'   => trans('admin.sqs_prefix_label'),
			'type'    => 'text',
			'default' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
			'hint'    => trans('admin.sqs_prefix_hint'),
			'wrapper' => [
				'class' => 'col-md-12 sqs',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'       => 'sqs_queue',
			'label'      => trans('admin.sqs_queue_label'),
			'type'       => 'text',
			'default'    => $queue,
			'attributes' => [
				'disabled' => true,
			],
			'hint'       => trans('admin.sqs_queue_hint'),
			'wrapper'    => [
				'class' => 'col-md-12 sqs',
			],
			'tab'        => $tabName,
		];
		$fields[] = [
			'name'    => 'sqs_suffix',
			'label'   => trans('admin.sqs_suffix_label'),
			'type'    => 'text',
			'default' => '',
			'hint'    => trans('admin.sqs_suffix_hint'),
			'wrapper' => [
				'class' => 'col-md-12 sqs',
			],
			'tab'     => $tabName,
		];
		$fields[] = [
			'name'    => 'sqs_region',
			'label'   => trans('admin.sqs_region_label'),
			'type'    => 'text',
			'default' => 'us-east-1',
			'hint'    => trans('admin.sqs_region_hint'),
			'wrapper' => [
				'class' => 'col-md-12 sqs',
			],
			'tab'     => $tabName,
		];
		
		$tabName = trans('admin.webp_format_title');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'webp_format_title',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'  => 'webp_format',
			'label' => trans('admin.webp_format_label'),
			'type'  => 'checkbox_switch',
			'hint'  => trans('admin.webp_format_hint'),
			'tab'   => $tabName,
		];
		
		$tabName = trans('admin.lazy_loading_sep_value');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'lazy_loading_sep',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'  => 'lazy_loading_activation',
			'label' => trans('admin.lazy_loading_activation_label'),
			'type'  => 'checkbox_switch',
			'hint'  => trans('admin.lazy_loading_activation_hint'),
			'tab'   => $tabName,
		];
		
		$tabName = trans('admin.minify_html_sep_value');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'minify_html_sep',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'  => 'minify_html_activation',
			'label' => trans('admin.minify_html_activation_label'),
			'type'  => 'checkbox_switch',
			'hint'  => trans('admin.minify_html_activation_hint'),
			'tab'   => $tabName,
		];
		
		$tabName = trans('admin.route_config_cache_sep_value');
		if (self::getPanelTabsType() == 'vertical') {
			$fields[] = [
				'name'  => 'route_config_cache_sep',
				'type'  => 'custom_html',
				'value' => $tabName,
				'tab'   => $tabName,
			];
		}
		$fields[] = [
			'name'  => 'route_config_cache_info',
			'type'  => 'custom_html',
			'value' => trans('admin.route_config_cache_info_value'),
			'tab'   => $tabName,
		];
		
		// Display button based on cache status
		$routeCachePath = base_path('bootstrap/cache/routes-v7.php');
		$routeCacheExists = file_exists($routeCachePath);
		$routeCacheEnabled = (self::$currentValues['route_cache_enabled'] ?? '0') == '1';
		
		$fields[] = [
			'name'    => 'route_cache_enabled',
			'label'   => trans('admin.route_cache_enabled_label'),
			'type'    => 'checkbox_switch',
			'hint'    => trans('admin.route_cache_enabled_hint'),
			'wrapper' => [
				'class' => 'col-md-6',
			],
			'newline' => !$routeCacheEnabled && !$routeCacheExists,
			'tab'     => $tabName,
		];
		
		if ($routeCacheEnabled) {
			if ($routeCacheExists) {
				// Cache enabled + a file exists = Show regenerate button
				$fields[] = [
					'name'    => 'regenerate_route_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.regenerate_route_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/route-cache/regenerate')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			} else {
				// Cache enabled + a file does not exist = Show generate button
				$fields[] = [
					'name'    => 'generate_route_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.generate_route_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/route-cache/generate')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			}
		} else {
			if ($routeCacheExists) {
				// Cache disabled + a file exists = Show clear button
				$fields[] = [
					'name'    => 'clear_route_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.clear_route_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/route-cache/clear')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			}
		}
		
		// Display button based on cache status
		$configCachePath = base_path('bootstrap/cache/config.php');
		$configCacheExists = file_exists($configCachePath);
		$configCacheEnabled = (self::$currentValues['config_cache_enabled'] ?? '0') == '1';
		
		$fields[] = [
			'name'    => 'config_cache_enabled',
			'label'   => trans('admin.config_cache_enabled_label'),
			'type'    => 'checkbox_switch',
			'hint'    => trans('admin.config_cache_enabled_hint'),
			'wrapper' => [
				'class' => 'col-md-6',
			],
			'newline' => !$configCacheEnabled && !$configCacheExists,
			'tab'     => $tabName,
		];
		
		if ($configCacheEnabled) {
			if ($configCacheExists) {
				// Cache enabled + a file exists = Show regenerate button
				$fields[] = [
					'name'    => 'regenerate_config_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.regenerate_config_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/config-cache/regenerate')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			} else {
				// Cache enabled + a file does not exist = Show generate button
				$fields[] = [
					'name'    => 'generate_config_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.generate_config_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/config-cache/generate')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			}
		} else {
			if ($configCacheExists) {
				// Cache disabled + a file exists = Show clear button
				$fields[] = [
					'name'    => 'clear_config_cache_btn',
					'type'    => 'custom_html',
					'value'   => trans('admin.clear_config_cache_btn_value', [
						'actionUrl' => urlGen()->adminUrl('actions/config-cache/clear')
					]),
					'wrapper' => [
						'class' => 'col-md-6 mt-3',
					],
					'newline' => true,
					'tab'     => $tabName,
				];
			}
		}
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
