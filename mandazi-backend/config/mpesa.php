<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'), // sandbox or production
    'mode' => env('MPESA_MODE', 'simulation'), // real, simulation, or auto
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'passkey' => env('MPESA_PASSKEY'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
];