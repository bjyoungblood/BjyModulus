<?php
return array(
    'service_manager' => array(
        'invokables' => array (
            'bjymodulus_modules_service' => 'BjyModulus\Service\Modules'
        )
    ),
    'view_manager' => array(
       'template_path_stack' => array(
            __DIR__ . '/../view',
        ),

    ),
);
