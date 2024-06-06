<?php

declare(strict_types=1);

namespace Codeat3\BladeSolarIcons;

use BladeUI\Icons\Factory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class BladeSolarIconsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();

        $this->callAfterResolving(Factory::class, function (Factory $factory, Container $container) {
            $config = $container->make('config')->get('blade-solar-icons', []);

            $factory->add('solar-icons', array_merge(['path' => __DIR__ . '/../resources/svg'], $config));
        });
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blade-solar-icons.php', 'blade-solar-icons');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/svg' => public_path('vendor/blade-solar-icons'),
            ], 'blade-solar-icons'); // TDOO: update this alias to `blade-solar-icons` in next major release

            $this->publishes([
                __DIR__ . '/../config/blade-solar-icons.php' => $this->app->configPath('blade-solar-icons.php'),
            ], 'blade-solar-icons-config');
        }
    }
}
