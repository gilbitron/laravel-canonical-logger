<?php

namespace Gilbitron\LaravelCanonicalLogger\Listeners;

use Gilbitron\LaravelCanonicalLogger\Facades\CanonicalLogger;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueListener extends ServiceProvider
{
    public function boot(): void
    {
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            if (CanonicalLogger::canonicalLoggerEnabled()) {
                $payload['canonical_logger'] = array_merge($payload['canonical_logger'] ?? [], [
                    'request_id' => CanonicalLogger::requestId(),
                    'session_id' => session()?->getId(),
                    'user_id' => auth()->user()?->id,
                ]);
            }

            return $payload;
        });

        Queue::after(function (JobProcessed $event) {
            if (CanonicalLogger::canonicalLoggerEnabled()) {
                $this->canonicalLogForJobs($event, 'processed');
            }
        });

        Queue::failing(function (JobFailed $event) {
            if (CanonicalLogger::canonicalLoggerEnabled()) {
                $this->canonicalLogForJobs($event, 'failed');
            }
        });
    }

    private function canonicalLogForJobs(
        JobProcessed|JobFailed $event,
        string $status
    ): void {
        $logChannel = config('canonical-logger.log_channel');
        $logLevel = config('canonical-logger.log_level');

        $payload = [
            'type' => 'job',
            'environment' => app()->environment(),
            'id' => $event->job->uuid(),
            'status' => $status,
            'name' => $event->job->resolveName(),
            'basename' => class_basename($event->job->resolveName()),
            'connection' => $event->job->getConnectionName(),
            'queue' => $event->job->getQueue(),
            'attempts' => $event->job->attempts(),
            'max_tries' => $event->job->maxTries(),
            'max_exceptions' => $event->job->maxExceptions(),
            'timeout' => $event->job->timeout(),
            'retry_until' => $event->job->retryUntil(),
            'request_id' => $event->job->payload()['canonical_logger']['request_id'] ?? null,
            'session_id' => $event->job->payload()['canonical_logger']['session_id'] ?? null,
            'user_id' => $event->job->payload()['canonical_logger']['user_id'] ?? null,
        ];

        if ($event->exception ?? null) {
            $payload['exception_class'] = get_class($event->exception);
            $payload['exception_message'] = $event->exception->getMessage();
            $payload['exception_code'] = $event->exception->getCode();
            $payload['exception_file'] = $event->exception->getFile();
            $payload['exception_line'] = $event->exception->getLine();
        }

        Log::channel($logChannel)->{$logLevel}(
            'canonical-log-line',
            array_filter($payload, fn ($value) => $value !== null)
        );
    }
}
