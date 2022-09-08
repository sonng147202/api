<?php
return [
    'sms_service'    => env('SMS_SERVICE', 'esms'),
    'sms_sandbox'    => env('SMS_SANDBOX', 1),
    'esms' => [
        'api_key'    => env('ESMS_API_KEY', ''),
        'api_secret' => env('ESMS_API_SECRET', ''),
    ],
    'nexmo' => [
        'api_key'    => env('NEXMO_API_KEY', ''),
        'api_secret' => env('NEXMO_API_SECRET', ''),
    ],
    'env_apns'       => env('ENV_APNS', 'production'),
    'apns_apple'     => [
        'sandbox'    => env('APNS_APPLE_SANDBOX_PEM', 'cert_dev_noti.pem'),
        'production' => env('APNS_APPLE_PRODUCT_PEM', 'cert_dev_noti.pem')
    ],
    'google' => [
        'service' => env('ANDROID_PUSH_SERVICE', 'fcm'),
        'fcm' => [
            'api_key'  => env('FCM_PUSH_API_KEY', ''),
            'push_url' => env('FCM_PUSH_URL', 'https://fcm.googleapis.com/fcm/send')
        ],
        'gcm' => [
            'api_key'  => env('GCM_PUSH_API_KEY', ''),
            'push_url' => env('GCM_PUSH_URL', 'https://fcm.googleapis.com/fcm/send')
        ]
    ]
];