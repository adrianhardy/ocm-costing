<?php

return [
    'criteria' => [
        'acceptedConditions' => [
            '=', 'like', 'in'
        ]
    ],
    'cache'=>[
        'enabled'   => false,
        'minutes'   => 5,
        'repository'=> 'cache',
        'clean'     => [
            'enabled' => true,
            'on' => [
                'create'=>true,
                'update'=>true,
                'delete'=>true,
            ]
        ],
        'params' => [
            'skipCache'=>'skipCache'
        ],
        'allowed'=>[
            'only'  =>null,
            'except'=>null
        ]
    ]
];
