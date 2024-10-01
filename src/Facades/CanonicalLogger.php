<?php

namespace Gilbitron\LaravelCanonicalLogger\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool canonicalLoggerEnabled()
 * @method static string requestId()
 *
 * @see \Gilbitron\LaravelCanonicalLogger\CanonicalLogger
 */
class CanonicalLogger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-canonical-logger';
    }
}
