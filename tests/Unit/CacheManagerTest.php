<?php

namespace Tests\Unit;

use App\Helpers\Common\CacheManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    #[Test]
    public function it_respects_environment_specific_logging_settings_for_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'cache-manager.enable_logging' => true,
            'cache-manager.environments.production.enable_logging' => false,
        ]);

        $manager = new CacheManager();
        $method = new \ReflectionMethod(CacheManager::class, 'isLoggingEnabled');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($manager));
    }
}
