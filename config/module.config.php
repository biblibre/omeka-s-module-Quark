<?php

namespace Quark;

return [
    'controllers' => [
        'invokables' => [
            'Quark\Controller\Site\Ark' => Controller\Site\ArkController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'quark' => Service\ControllerPlugin\QuarkFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'ark' => [
                        'type' => \Zend\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/ark:',
                            'defaults' => [
                                '__NAMESPACE__' => 'Quark\Controller\Site',
                            ],
                        ],
                        'child_routes' => [
                            'id' => [
                                'type' => \Zend\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/:naan/:name[/:qualifier]',
                                    'options' => [
                                        'controller' => 'Ark',
                                        'action' => 'index',
                                    ],
                                    'constraints' => [
                                        'naan' => '[0-9]{5}',
                                        'name' => '[a-z0-9]+',
                                        'qualifier' => '[a-z0-9/.]+',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Quark\ArkManager' => Service\Ark\ManagerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
    ],
];
