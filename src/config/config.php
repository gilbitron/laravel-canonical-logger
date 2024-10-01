<?php

return [
    'enabled' => env('CANONICAL_LOGGER_ENABLED', true),

    'log_channel' => env('CANONICAL_LOGGER_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    'log_level' => env('CANONICAL_LOGGER_LOG_LEVEL', 'info'),
];
