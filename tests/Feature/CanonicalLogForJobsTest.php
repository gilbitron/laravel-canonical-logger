<?php

use Illuminate\Support\Facades\Log;
use Tests\Jobs\ExampleErrorJob;
use Tests\Jobs\ExampleJob;
use TiMacDonald\Log\LogEntry;
use TiMacDonald\Log\LogFake;

beforeEach(function () {
    LogFake::bind();
});

test('canonical line is logged for standard request', function () {
    ExampleJob::dispatchSync();

    Log::channel('stack')->assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
    );
});

test('canonical line is not logged when logging is disabled', function () {
    config(['canonical-logger.enabled' => false]);

    ExampleJob::dispatchSync();

    Log::assertNotLogged(fn (LogEntry $log) => $log->message === 'canonical-log-line');
});

test('canonical line has expected context', function () {
    ExampleJob::dispatchSync();

    Log::assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
        && $log->context['type'] === 'job'
        && expect(array_keys($log->context))->toContain(
            'type',
            'environment',
            'id',
            'status',
            'name',
            'basename',
            'connection',
            'queue',
        )
    );
});

test('canonical line context contains exception', function () {
    try {
        ExampleErrorJob::dispatchSync();
    } catch (Exception $e) {
    }

    Log::assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
        && $log->context['type'] === 'job'
        && expect(array_keys($log->context))->toContain(
            'exception_class',
            'exception_message',
            'exception_code',
            'exception_file',
            'exception_line',
        )
    );
});
