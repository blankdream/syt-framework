<?php

namespace think;

use Yaf\{Loader, Registry};
//注册命名空间自动加载路径
loader::getInstance()->registerNamespace([
    "app" => ROOT_PATH .  "/app",
    "think" => ROOT_PATH .  "/thinkphp/library/think",
]);

//加载公共函数
Loader::import(ROOT_PATH . '/app/functions.php');
//加载控制器基类
Loader::import(ROOT_PATH . '/thinkphp/library/think/Controller.php');

