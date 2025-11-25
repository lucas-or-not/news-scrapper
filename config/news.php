<?php

return [
    /*
    |--------------------------------------------------------------------------
    | News API Keys
    |--------------------------------------------------------------------------
    |
    | API keys for various news sources. These are loaded from environment
    | variables to keep sensitive data out of the database.
    |
    */
    'api_keys' => [
        'newsapi' => env('NEWSAPI_KEY'),
        'guardian' => env('GUARDIAN_API_KEY'),
        'nytimes' => env('NYTIMES_API_KEY'),
    ],
];
