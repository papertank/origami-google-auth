<?php

return [

    /** Options: service_account */
    'default' => 'service_account',

    'service_account' => [
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/google-auth/service-account-credentials.json')),
    ],

    'cache' => [
        /** Cache tags required */
        'enabled' => true,
        'options' => [],
    ],

];