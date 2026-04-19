<?php

return [

    'default' => env('COURIER_DRIVER', 'pathao'),

    /*
    |--------------------------------------------------------------------------
    | Pathao Courier
    |--------------------------------------------------------------------------
    */
    'pathao' => [
        'base_url'      => env('PATHAO_BASE_URL', 'https://api-hermes.pathao.com'),
        'client_id'     => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username'      => env('PATHAO_USERNAME'),
        'password'      => env('PATHAO_PASSWORD'),
        'store_id'      => env('PATHAO_STORE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Steadfast Courier
    |--------------------------------------------------------------------------
    */
    'steadfast' => [
        'base_url'   => env('STEADFAST_BASE_URL', 'https://portal.steadfast.com.bd/api/v1'),
        'api_key'    => env('STEADFAST_API_KEY'),
        'secret_key' => env('STEADFAST_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RedX Courier
    |--------------------------------------------------------------------------
    */
    'redx' => [
        'base_url' => env('REDX_BASE_URL', 'https://openapi.redx.com.bd/v1.0.0-beta'),
        'api_key'  => env('REDX_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CarryBee Courier
    |--------------------------------------------------------------------------
    */
    'carrybee' => [
        'base_url' => env('CARRYBEE_BASE_URL', 'https://api.carrybee.com.bd/api/v1'),
        'api_key'  => env('CARRYBEE_API_KEY'),
    ],

];
