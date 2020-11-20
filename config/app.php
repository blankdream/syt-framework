<?php
//公共配置
return [
    'rootPath'  => ROOT_PATH,
    'appPath'  => ROOT_PATH . DIRECTORY_SEPARATOR . 'app',
    'directory'=>ROOT_PATH . DIRECTORY_SEPARATOR . 'app',
    'modules'    => 'Index,api',
    'library' => [
        'directory'=>ROOT_PATH . DIRECTORY_SEPARATOR.'extend',
        // 'namespace'=>[
        //     "\\app" => ROOT_PATH .  "/app",
        //     "\\think" => ROOT_PATH .  "/thinkphp/library/think",
        // ]
    ],
    'bootstrap' =>ROOT_PATH . DIRECTORY_SEPARATOR. 'app'. DIRECTORY_SEPARATOR.'Bootstrap.php',
    'dispatcher' => [
        'defaultController' => 'Index',
        'throwException' => TRUE,
        'catchException' => TRUE,
        
    ]
];