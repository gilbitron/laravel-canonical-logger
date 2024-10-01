<?php

namespace Tests;

use Exception;
use Gilbitron\LaravelCanonicalLogger\LaravelCanonicalLoggerServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Route;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelCanonicalLoggerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config) {
            $config->set('canonical-logger.enabled', true);
            $config->set('canonical-logger.log_channel', 'stack');
            $config->set('canonical-logger.log_level', 'info');
        });
    }

    protected function defineRoutes($router): void
    {
        Route::get('/', function () {
            return 'Hello, world!';
        })->name('home');

        Route::get('/error', function () {
            throw new Exception('An error occurred');
        })->name('error');
    }
}
