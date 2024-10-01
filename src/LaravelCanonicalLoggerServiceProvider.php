<?php

namespace Gilbitron\LaravelCanonicalLogger;

use Gilbitron\LaravelCanonicalLogger\Http\Middleware\CanonicalLogForRequests;
use Gilbitron\LaravelCanonicalLogger\Listeners\QueueListener;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class LaravelCanonicalLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'canonical-logger');

        $this->app->bind('laravel-canonical-logger', fn () => new CanonicalLogger);
        $this->app->register(QueueListener::class);
    }

    public function boot(Kernel $kernel): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/config.php' => config_path('canonical-logger.php'),
            ], 'config');
        }

        $kernel->prependMiddleware(CanonicalLogForRequests::class);
    }
}
