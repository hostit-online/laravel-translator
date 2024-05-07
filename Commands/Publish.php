<?php

namespace HostitOnline\LaravelTranslator\Commands;

use HostitOnline\LaravelTranslator\TranslationsServiceProvider;
use Illuminate\Console\Command;

class Publish extends Command
{
    protected $signature = 'laravelTranslator:publish {--config} {--migrations}';
    protected $description = "Publish various assets of the 'Laravel translations' package.";

    public function handle(): void
    {
        if ($this->option('assets')) {
            $this->call('casts:publish', ['--assets' => true]);
        }

        if ($this->option('config')) {
            $this->call('vendor:publish', [
                '--provider' => TranslationsServiceProvider::class,
                '--tag' => ['config'],
                '--force' => true,
            ]);
        }

        if ($this->option('migrations')) {
            $this->call('vendor:publish', [
                '--provider' => TranslationsServiceProvider::class,
                '--tag' => ['migrations'],
                '--force' => true,
            ]);
        }
    }
}
