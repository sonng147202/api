<?php

return [
    'name' => 'Core',
    'resource_url' => env('EROSCARE_RESOURCE_URL', 'http://resource.eroscare.com'),
    'api_url'      => env('EROSCARE_API_URL', 'http://api-eroscare.paditech.com'),
    'group_id' => [
        'super_admin' => 1,
        'staff' => 2,
        'agency' => 3
    ],
    'user_status' => [
        'active' => 1,
        'lock' => 2,
        'confirm' => 3,
        'reject' => 4,
        'locked' => 5
    ],
    'password_default' => 'medici6868'
];
