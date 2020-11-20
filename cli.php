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

(new Yaf\Application($config))->bootstrap();

if (!isset($argv[1])) {
    exit("Please enter the route to execute. Example: the php cli.php Index/Index!\n");
}

$routeArr = explode('/', $argv[1]);
if (count($routeArr) != 2) {
    exit("Please enter the route to execute. Example: the php cli.php Index/Index!\n");
}

$controllerName = $routeArr[0];
$actionName     = $routeArr[1];

$request = new Yaf\Request_Simple('CLI', 'Cli', $controllerName, $actionName);
Yaf\Application::app()->getDispatcher()->returnResponse(true)->dispatch($request);
