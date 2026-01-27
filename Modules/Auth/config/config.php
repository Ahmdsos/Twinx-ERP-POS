<?php

return [
    'name' => 'Auth',
    'description' => 'Authentication and Authorization module for Twinx ERP',

    // Token expiration in minutes (0 = never expires)
    'token_expiration' => env('AUTH_TOKEN_EXPIRATION', 0),

    // Maximum tokens per user (0 = unlimited)
    'max_tokens_per_user' => env('AUTH_MAX_TOKENS', 5),
];
