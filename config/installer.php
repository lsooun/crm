<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
    'requirements' => [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer'
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/app/'           => '775',
        'storage/framework/'     => '775',
        'storage/logs/'          => '775',
        'bootstrap/cache/'       => '775',
        'public/pdf/'                   => '775',
        'public/uploads/'               => '775',
        'public/uploads/avatar/'        => '775',
        'public/uploads/company/'       => '775',
        'public/uploads/contract/'      => '775',
        'public/uploads/customer/'      => '775',
        'public/uploads/pdf/'           => '775',
        'public/uploads/products/'      => '775',
        'public/uploads/site/'          => '775'
    ]
];
