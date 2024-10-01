<?php

namespace Gilbitron\LaravelCanonicalLogger;

use Illuminate\Support\Str;

class CanonicalLogger
{
    final public static function canonicalLoggerEnabled(): bool
    {
        return (bool) config('canonical-logger.enabled', false);
    }

    final public static function requestId(): string
    {
        return once(function () {
            return request()->header('x-request-id') ?: (string) Str::uuid();
        });
    }
}
