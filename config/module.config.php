<?php

namespace Quark;

return [
    'service_manager' => [
        'factories' => [
            'Quark\ArkManager' => Service\ArkManagerFactory::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
];
