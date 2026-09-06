<?php

return [
    'prototype_modules_enabled' => env('STIFLOW_PROTOTYPE_MODULES', true),

    'branch' => [
        'branch_code' => env('BRANCH_CODE', 'CABANG-SETUP'),
        'brand_name' => env('APP_NAME', 'STIFLOW Cabang'),
        'contact' => env('BRANCH_CONTACT'),
        'address' => env('BRANCH_ADDRESS'),
        'bank_name' => env('MANUAL_TRANSFER_BANK'),
        'bank_account' => env('MANUAL_TRANSFER_ACCOUNT'),
        'bank_account_name' => env('MANUAL_TRANSFER_HOLDER'),
        'locale' => env('BRANCH_LOCALE', 'id_ID'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
        'currency' => env('APP_CURRENCY', 'IDR'),
    ],

    'voucher' => [
        'name' => env('VOUCHER_PRODUCT_NAME', 'Voucher STIFIN Satuan'),
        'slug' => env('VOUCHER_PRODUCT_SLUG', 'voucher-stifin-satuan'),
        'unit_price' => (int) env('VOUCHER_UNIT_PRICE', 100000),
        'min_qty' => (int) env('VOUCHER_MIN_QTY', 1),
        'max_qty' => (int) env('VOUCHER_MAX_QTY', 100),
        'presets' => [1, 5, 10, 25, 50],
    ],
];
