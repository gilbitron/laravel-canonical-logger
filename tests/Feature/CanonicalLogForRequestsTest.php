<?php

use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogEntry;
use TiMacDonald\Log\LogFake;

beforeEach(function () {
    LogFake::bind();
});

test('canonical line is logged for standard request', function () {
    $this->get(route('home'))->assertOk();

    Log::channel('stack')->assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
    );
});

test('canonical line is not logged when logging is disabled', function () {
    config(['canonical-logger.enabled' => false]);

    $this->get(route('home'))->assertOk();

    Log::assertNotLogged(fn (LogEntry $log) => $log->message === 'canonical-log-line');
});

test('canonical line uses custom config', function () {
    config([
        'canonical-logger.log_channel' => 'custom_channel',
        'canonical-logger.log_level' => 'debug',
    ]);

    $this->get(route('home'))->assertOk();

    Log::channel('custom_channel')->assertLogged(fn (LogEntry $log) => $log->level === 'debug'
        && $log->message === 'canonical-log-line'
    );
});

test('canonical line has expected context', function () {
    $this->get(route('home'))->assertOk();

    Log::assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
        && $log->context['type'] === 'request'
        && expect(array_keys($log->context))->toContain(
            'type',
            'environment',
            'request_id',
            'http_method',
            'http_uri',
            'http_status',
            'http_response_time',
            'url',
            'ip_address',
            'user_agent',
        )
    );
});

test('canonical line context contains exception', function () {
    $this->get(route('error'))->assertStatus(500);

    Log::assertLogged(fn (LogEntry $log) => $log->level === 'info'
        && $log->message === 'canonical-log-line'
        && $log->context['type'] === 'request'
        && expect(array_keys($log->context))->toContain(
            'exception_class',
            'exception_message',
            'exception_code',
            'exception_file',
            'exception_line',
        )
    );
});
