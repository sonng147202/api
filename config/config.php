<?php

return [
    'name' => 'Insurance',
    'price_attribute' => [
        'data_type' => [
            'text'     => 'Text',
            'number'   => 'Kiểu số',
            'currency' => 'Kiểu tiền tệ',
            'select'   => 'Select box',
            'checkbox' => 'Checkbox'
        ]
    ],
    'delay_get_file' => env('DELAY_GET_FILE', 5), // seconds
    //VIB
    'dtc_ma' => env('DTAC_MA','EBHKD01'),
    'ma_dvi' => env('MA_DVI','EBHKD01'),
    'nsd'     => env('NSD','EBHKD01'),
    'dtac_key'=> env('DTAC_KEY','FYiQSnNvEdHlISDxiNxG'),
    'auth_key' => env('AUTH_KEY', 'EBHKD01-FYiQSnNvEdHlISDxiNxG'),

    'max_get_file_times'  => env('MAX_GET_FILE_TIMES',3),

    //email send when get file success
    'email_isurance'    => env('EMAIL_ISURANCE','policy@ebaohiem.com'),
    'email_notify_provide' => env('INSURANCE_EMAIL_NOTICE_PROVIDE', 'services@eroscare.com'),
    // create contract
    'id_create_contract' => env('ID_CREATE_CONTRACT','3,4'),
    //update payment
    'id_update_payment'  => env('ID_UPDATE_PAYMENT',3),
    // Provide contract permission
    'provide_contract_permission' => env('PROVIDE_CONTRACT_PERMISSION', 102),
    'confirm_payment_permission' => env('CONFIRM_PAYMENT_PERMISSION', 96)
];
