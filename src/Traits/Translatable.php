<?php

namespace HostitOnline\LaravelTranslator\Traits;

use HostitOnline\LaravelTranslator\Scopes\TranslatableScope;

trait Translatable
{
    public static function bootTranslatable(): void
    {
        static::addGlobalScope(new TranslatableScope());
    }
}
