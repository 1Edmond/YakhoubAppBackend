<?php

return [
    'airtel_money' => [
        'api_url' => env('AIRTEL_MONEY_API_URL', ''),
        'api_key' => env('AIRTEL_MONEY_API_KEY', ''),
        'api_secret' => env('AIRTEL_MONEY_API_SECRET', ''),
    ],
    'moov_money' => [
        'api_url' => env('MOOV_MONEY_API_URL', ''),
        'api_key' => env('MOOV_MONEY_API_KEY', ''),
        'api_secret' => env('MOOV_MONEY_API_SECRET', ''),
    ],
    'cancellation_fee' => 1000,
    'nni_document_storage' => 'local',
];
