<?php

namespace Quark;

return [
    'service_manager' => [
        'factories' => [
            'Quark\ArkManager' => Service\ArkManagerFactory::class,
        ],
    ],
];
