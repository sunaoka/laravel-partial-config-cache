<?php

declare(strict_types=1);

namespace Sunaoka\Laravel\PartialConfigCache;

use Sunaoka\Laravel\PartialConfigCache\Console\Commands\ConfigCacheCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/partial-config.php',
            'partial-config'
        );
    }

    public function boot(): void
    {
        $this->publishes(
            [__DIR__.'/../config/partial-config.php' => config_path('partial-config.php')],
            'partial-config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands(ConfigCacheCommand::class);
        }
    }
}
