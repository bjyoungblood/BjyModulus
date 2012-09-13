<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'bjymodulus_modules_controller' => 'BjyModulus\Controller\ModulesController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'bjymodulus' => array(
                'type'    => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route'    => '/installed-modules',
                    'defaults' => array(
                        'controller'    => 'bjymodulus_modules_controller',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'ZendSkeletonModule' => __DIR__ . '/../view',
        ),
    ),
);
