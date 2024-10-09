<?php

return [
    /**
     * Enable or disable the canonical logger.
     */
    'enabled' => env('CANONICAL_LOGGER_ENABLED', true),

    /**
     * The log channel to use for the canonical logger.
     */
    'log_channel' => env('CANONICAL_LOGGER_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    /**
     * The log level to use for the canonical logger.
     */
    'log_level' => env('CANONICAL_LOGGER_LOG_LEVEL', 'info'),
];
