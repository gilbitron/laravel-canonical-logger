<?php

namespace Gilbitron\LaravelCanonicalLogger\Http\Middleware;

use Closure;
use Gilbitron\LaravelCanonicalLogger\Facades\CanonicalLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CanonicalLogForRequests
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->exception) {
            $request->attributes->set('handled_exception', $response->exception);
        }

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! CanonicalLogger::canonicalLoggerEnabled()) {
            return;
        }

        $logChannel = config('canonical-logger.log_channel');
        $logLevel = config('canonical-logger.log_level');

        $session = rescue(fn () => $request->session(), null, false);

        $payload = [
            'type' => 'request',
            'environment' => app()->environment(),
            'request_id' => CanonicalLogger::requestId(),
            'http_method' => $request->method(),
            'http_uri' => $request->getRequestUri(),
            'http_status' => $response->getStatusCode(),
            'http_response_time' => round(microtime(true) - LARAVEL_START, 4),
            'route_name' => $request->route()?->getName(),
            'user_id' => $request->user()?->id,
            'session_id' => $session?->getId(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'x_forwarded_for' => $request->header('x-forwarded-for'),
            'user_agent' => $request->userAgent(),
        ];

        if ($exception = $request->attributes->get('handled_exception')) {
            $payload['exception_class'] = get_class($exception);
            $payload['exception_message'] = $exception->getMessage();
            $payload['exception_code'] = $exception->getCode();
            $payload['exception_file'] = $exception->getFile();
            $payload['exception_line'] = $exception->getLine();
        }

        Log::channel($logChannel)->{$logLevel}(
            'canonical-log-line',
            array_filter($payload, fn ($value) => $value !== null)
        );
    }
}
