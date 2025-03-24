<?php

declare(strict_types=1);

namespace Sunaoka\Laravel\PartialConfigCache\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Testing\PendingCommand;
use Orchestra\Testbench\TestCase;
use Sunaoka\Laravel\PartialConfigCache\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        tap($app['config'], static function (Repository $config) {
            $config->set('partial-config.environments', [
                'APP_ENV',
            ]);
        });
    }

    public function test_register(): void
    {
        $artisan = $this->artisan('partial:config:cache');
        self::assertInstanceOf(PendingCommand::class, $artisan);

        $artisan
            ->assertOk()
            ->expectsOutputToContain('Configuration partial cached successfully.');

        self::assertInstanceOf(Application::class, $this->app);
        $configPath = $this->app->getCachedConfigPath();

        $cache = file_get_contents($configPath);
        self::assertStringContainsString("'env' => \$_ENV['APP_ENV'] ?? \$_SERVER['APP_ENV'] ?? null,", (string) $cache);

        $config = require $configPath;
        self::assertIsArray($config);
        self::assertIsArray($config['app']);
        self::assertNull($config['app']['env']);

        $_ENV['APP_ENV'] = 'env';
        $config = require $configPath;
        self::assertIsArray($config);
        self::assertIsArray($config['app']);
        self::assertSame('env', $config['app']['env']);

        unset($_ENV['APP_ENV']);

        $_SERVER['APP_ENV'] = 'server';
        $config = require $configPath;
        self::assertIsArray($config);
        self::assertIsArray($config['app']);
        self::assertSame('server', $config['app']['env']);
    }
}
