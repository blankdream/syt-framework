<?php
//公共配置
return [
    'rootPath'  => ROOT_PATH,
    'appPath'  => ROOT_PATH . DIRECTORY_SEPARATOR . 'app',
    //'directory'=>ROOT_PATH . DIRECTORY_SEPARATOR . 'app',
    'modules'    => 'Index,api',
    'dispatcher' => [
        'defaultController' => 'Index',
        'throwException' => TRUE,
        'catchException' => TRUE,
        
    ]
];