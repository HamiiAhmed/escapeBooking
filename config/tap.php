<?php
return [
    'public_key' => env('TAP_MODE') === 'live' ? env('TAP_PUBLIC_KEY') : env('TAP_TEST_PUBLIC_KEY'),
    'secret_key' => env('TAP_MODE') === 'live' ? env('TAP_SECRET_KEY') : env('TAP_TEST_SECRET_KEY'),
    'mode' => env('TAP_MODE', 'test'),
    'gosell_url' => 'https://gosell-api.tap.company/v1.1/charges',
];
