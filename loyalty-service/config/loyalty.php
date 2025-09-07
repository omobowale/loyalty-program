<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cashback Settings
    |--------------------------------------------------------------------------
    |
    | Percentage of purchase amount to return as cashback.
    | For example, 0.05 = 5%
    |
    */
    'cashback_rate' => env('LOYALTY_CASHBACK_RATE', 0.05),
];
