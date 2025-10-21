<?php

return [
    /*
    |--------------------------------------------------------------------------
    | General Information
    |--------------------------------------------------------------------------
    |
    | General information that used by taut shipping package
    |
    */
    'brand_name' => 'Brand Name',

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | List of all drivers supported by this package.
    |
    | Supported: apikurir
    |
    */

    'drivers' => [
        'apikurir',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transitions
    |--------------------------------------------------------------------------
    |
    | You can customize and add extra process from every state of payment
    |
    | Available State: ToCanceled, ToDelivered, ToDelivering, ToDraft
    |                  ToFailed, ToLost, ToRequested, ToReturned
    */

    'transitions_namespace' => 'App\\Transitions\\Shipping',

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | All required credentials
    |
    | note: replace using env() for safety
    */

    'apikurir_username' => null,
    'apikurir_password' => null,
    'apikurir_webhook_secret' => null,
];
