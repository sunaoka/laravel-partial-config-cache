<?php

declare(strict_types=1);

namespace Sunaoka\Laravel\PartialConfigCache\Console\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'partial:config:cache')]
class ConfigCacheCommand extends Command
{
    protected $signature = 'partial:config:cache';

    protected $description = 'Create a partial cache file for faster configuration loading';

    public function __construct(protected Filesystem $files, protected Repository $config)
    {
        parent::__construct();
    }

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        /** @var string[] $names */
        $names = (array) $this->config->get('partial-config.environments');

        foreach ($names as $name) {
            $_ENV[$name] = "### {$name} ###";
            $_SERVER[$name] = "### {$name} ###";
        }

        $this->callSilent('config:cache');

        $configPath = $this->laravel->getCachedConfigPath();

        $cache = str_replace(
            array_map(static fn (string $name) => "'### {$name} ###'", $names),
            array_map(static fn (string $name) => "\$_ENV['{$name}'] ?? \$_SERVER['{$name}'] ?? null", $names),
            $this->files->get($configPath)
        );

        $this->files->put($configPath, $cache);

        foreach ($names as $name) {
            unset($_ENV[$name], $_SERVER[$name]);
        }

        $this->components->info('Configuration partial cached successfully.');
    }
}
