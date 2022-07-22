<?php

declare(strict_types=1);

return [

    /**
     * The service that should be used to verify users by phone number.
     *
     * Supported: 'twilio', 'null'
     */
    'driver' => env('VERIFY_BY_PHONE_DRIVER', 'twilio'),

    'services' => [

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'verify_sid' => env('TWILIO_VERIFY_SID'),
        ],

    ],
];
