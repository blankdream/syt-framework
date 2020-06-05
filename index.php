<?php

define('ROOT_PATH', dirname(__FILE__));

// 自动读取配置文件
$dir = ROOT_PATH . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR;
$files = is_dir( $dir) ? scandir($dir) : [];
$config=[];
foreach ($files as $file) {
    $nameExt = explode('.' , $file);
    if (isset($nameExt[1]) && 'php' == $nameExt[1]) {
        if(isset($config[$nameExt[0]])){
            array_merge($config[$nameExt[0]], include $dir.$file);
        }else{
            $config[$nameExt[0]] = include $dir.$file;
        }
    }
}
//print_r($config);die;
$application = new Yaf\Application($config);
$application->bootstrap()->run();


