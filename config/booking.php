<?php

return [
    'slots' => ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'],

    'max_advance_days' => 30,

    'min_lead_minutes' => 60,

    'taxi_fee' => 15.00,

    'payment_channels' => [
        'fpx' => [
            ['code' => 'maybank2u', 'label' => 'Maybank2u'],
            ['code' => 'cimb', 'label' => 'CIMB Clicks'],
            ['code' => 'public_bank', 'label' => 'Public Bank'],
            ['code' => 'rhb', 'label' => 'RHB Now'],
            ['code' => 'bank_islam', 'label' => 'Bank Islam'],
        ],
        'ewallet' => [
            ['code' => 'tng', 'label' => "Touch 'n Go"],
            ['code' => 'grabpay', 'label' => 'GrabPay'],
            ['code' => 'boost', 'label' => 'Boost'],
            ['code' => 'shopee_pay', 'label' => 'ShopeePay'],
        ],
    ],
];
