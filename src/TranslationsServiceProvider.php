<?php

namespace HostitOnline\LaravelTranslator;

use HostitOnline\LaravelTranslator\Commands\Publish;
use Illuminate\Support\ServiceProvider;

class TranslationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/laravel-translation.php';
        $this->mergeConfigFrom($configPath, 'laravel-translation');

        $this->publishes([
            $configPath => config_path('laravel-translation.php'),
        ], "config");

        $this->publishesMigrations([
            __DIR__.'/../Database/Migrations' => database_path('migrations'),
        ]);
    }
}
