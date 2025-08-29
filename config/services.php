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

    'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'env' => env('MPESA_ENV'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'lipa_na_mpesa_passkey' => env('MPESA_LIPA_NA_MPESA_PASSKEY'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
        'validation_url' => env('MPESA_VALIDATION_URL'),
        'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
        'stk_push_url' => env('MPESA_STK_PUSH_URL'),
        'stk_query_url' => env('MPESA_STK_QUERY_URL'),
        'c2b_register_url' => env('MPESA_C2B_REGISTER_URL'),
        'b2c_url' => env('MPESA_B2C_URL'),
        'b2b_url' => env('MPESA_B2B_URL'),
        'account_balance_url' => env('MPESA_ACCOUNT_BALANCE_URL'),
        'reversal_url' => env('MPESA_REVERSAL_URL'),
        'transaction_status_url' => env('MPESA_TRANSACTION_STATUS_URL'),
        'validation_url' => env('MPESA_VALIDATION_URL'),
        'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
        'timeout_url' => env('MPESA_TIMEOUT_URL'),
        'result_url' => env('MPESA_RESULT_URL'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
        'party_a' => env('MPESA_TEST_PARTY_A'),
        'party_b' => env('MPESA_TEST_PARTY_B'),
        'phone_number' => env('MPESA_TEST_PHONE_NUMBER'),
    ],

];
