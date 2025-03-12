<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'rewardup' => [
        'base_url' => env('REWARD_BASE_URL', 'https://api.rewardup.io'),
        'key' => env('REWARD_KEY'),
    ],

    'clover' => [
        'base_url' => env('CLOVER_BASE_URL', 'https://api.clover.com'),
        'api_key' => env('CLOVER_API_KEY'),
        'merchant_id' => env('CLOVER_MERCHANT_ID'),
        'order_type' => env('CLOVER_ORDER_TYPE'),
        'test_mode' => env('CLOVER_TEST_MODE', true),
        'tender_id' => env('CLOVER_TENDER_ID'),
        'ecommerce_public_api_key' => env('CLOVER_ECOMMERCE_PUBLIC_KEY'),
        'ecommerce_private_api_key' => env('CLOVER_ECOMMERCE_PRIVATE_KEY'),
    ],

];