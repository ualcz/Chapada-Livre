<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\Common\JsonUtils;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	// Directories
	File::deleteDirectory(public_path('assets/plugins/sweetalert2/11.21.0/'));
	File::deleteDirectory(resource_path('views/setup/install/site_info/'));
	
	// Files
	File::delete(app_path('Jobs/GeneratePostCollectionThumbnails.php'));
	File::delete(app_path('Jobs/GeneratePostThumbnails.php'));
	File::delete(app_path('Jobs/GenerateThumbnail.php'));
	
	File::delete(resource_path('views/setup/install/cron_jobs.blade.php'));
	File::delete(resource_path('views/setup/install/database_import.blade.php'));
	File::delete(resource_path('views/setup/install/database_info.blade.php'));
	File::delete(resource_path('views/setup/install/site_info.blade.php'));
	
	// Plugins
	File::deleteDirectory(base_path('extras/plugins/'));
	File::deleteDirectory(app_path('Providers/PluginsService/'));
	File::deleteDirectory(app_path('Exceptions/Handler/Plugin/'));
	File::deleteDirectory(storage_path('framework/plugins/'));
	File::delete(app_path('Helpers/Services/Functions/plugin.php'));
	File::delete(app_path('Http/Controllers/Web/Admin/PluginController.php'));
	File::delete(app_path('Http/Controllers/Web/Front/Post/Show/Traits/ReviewsPlugin.php'));
	File::delete(app_path('Http/Requests/Admin/PluginRequest.php'));
	File::delete(app_path('Models/Post/ReviewsPlugin.php'));
	File::delete(app_path('Providers/PluginServiceProvider.php'));
	File::delete(resource_path('views/admin/plugins.blade.php'));
	
	if (file_exists(database_path('seeders/Factories/Traits/PluginTrait.php'))) {
		File::delete(database_path('seeders/Factories/Traits/PluginTrait.php'));
	}
	
	if (file_exists(base_path('cmdCachePurge.php'))) {
		File::delete(base_path('cmdCachePurge.php'));
	}
	if (file_exists(base_path('cmdDemoDbSeed.php'))) {
		File::delete(base_path('cmdDemoDbSeed.php'));
	}
	if (file_exists(base_path('cmdDemoListingsPurge.php'))) {
		File::delete(base_path('cmdDemoListingsPurge.php'));
	}
	if (file_exists(base_path('cmdListingsPurge.php'))) {
		File::delete(base_path('cmdListingsPurge.php'));
	}
	if (file_exists(base_path('cmdScheduleRun.php'))) {
		File::delete(base_path('cmdScheduleRun.php'));
	}
	
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	//...
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
