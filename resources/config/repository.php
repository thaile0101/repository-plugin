<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Repository Pagination Limit Default
    |--------------------------------------------------------------------------
    |
    */
    'pagination' => [
        'limit' => 15
    ],

    /*
    |--------------------------------------------------------------------------
    | Generator Config
    |--------------------------------------------------------------------------
    |
    */
    'generator'  => [
        'basePath'      => app_path(),
        'rootNamespace' => 'App\\',
        'paths'         => [
            'repositories' => 'Repositories/Eloquents',
            'interfaces'   => 'Repositories/Contracts',
            'models'       => 'Models',
        ]
    ]

];