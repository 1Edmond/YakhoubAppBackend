<?php

return [
    // CDC 3.6.1 : livraison gratuite pour toutes les commandes
    'free_delivery' => (bool) env('MULTISHOP_FREE_DELIVERY', true),

    // CDC 3.4.6 : annulation avec frais
    'cancellation_fee' => (float) env('MULTISHOP_CANCELLATION_FEE', 1000),
    'cancellable_statuses' => ['pending', 'confirmed', 'processing'], // avant "out_for_delivery" (Expédiée)
    'fee_free_statuses' => ['pending'],                                // pas de frais tant que le vendeur n'a pas confirmé
    'refund_paid_orders_to_wallet' => (bool) env('MULTISHOP_REFUND_TO_WALLET', true),

    // CDC 3.4.7 : 2 allers-retours maximum
    'max_negotiation_rounds' => 2,

    // Format NNI à CONFIRMER avec le format officiel tchadien
    'nni_regex' => env('MULTISHOP_NNI_REGEX', '/^[A-Za-z0-9]{8,20}$/'),

    'transfer_timezone' => env('MULTISHOP_TIMEZONE', 'Africa/Ndjamena'),

    // Paiement API Airtel / Moov : désactivé tant que les clés n'existent pas
    'payments' => [
        'airtel' => [
            'enabled' => (bool) env('AIRTEL_MONEY_ENABLED', false),
            'api_key' => env('AIRTEL_MONEY_API_KEY'),
            'webhook_secret' => env('AIRTEL_MONEY_WEBHOOK_SECRET'),
        ],
        'moov' => [
            'enabled' => (bool) env('MOOV_MONEY_ENABLED', false),
            'api_key' => env('MOOV_MONEY_API_KEY'),
            'webhook_secret' => env('MOOV_MONEY_WEBHOOK_SECRET'),
        ],
    ],
];
