<?php

//declare(strict_types=1);
namespace think;

class Console
{

    public static function println($strings)
    {
        echo $strings . PHP_EOL;
    }

    public static function echoSuccess($msg)
    {
        self::println('[' . date('Y-m-d H:i:s') . '] [INFO] ' . "\033[32m{$msg}\033[0m");
    }

    public static function echoError($msg)
    {
        self::println('[' . date('Y-m-d H:i:s') . '] [ERROR] ' . "\033[31m{$msg}\033[0m");
    }

    public static function run()
    {
        //self::welcome();
        global $argv;
        $command =$argv[1];
        switch ($command) {
            case 'start':
            case 'restart':
            case 'status':
            case 'stop':
            case 'reload':
                $servers = include ROOT_PATH . '/config/servers.php';
                if(isset($argv[2]) && isset($servers[$argv[2]])){
                    self::$command($servers[$argv[2]],$argv[2]);
                }else{
                    die("server is not exist, you can use {$argv[0]} {$argv[1]} [http, ws]");
                } 
            break;
            default:
                if (isset($config['commnd'][$command])) {
                    $className =  $config['commnd'][$command];
                    new $className($argv);
                } else {
                    die("command is not exist");
                }
        }
    }

    public static function start($config,$server)
    {
        //检测是否已启动
        if(isset($config['pid_file']) && $pid = @file_get_contents($config['pid_file'])){
            swoole_process::kill($pid, 0) && die("Framework has been started!" . PHP_EOL);
        }

        //检测端口是否可用
        // $socket = @stream_socket_server("tcp://{$config['host']}:{$config['port']}", $errno, $errstr);
        // if ($socket) {
        //     fclose($socket);
        //     unset($socket);
        // }else{
        //     die('Port is occupied!' . PHP_EOL . "Starting Failed!" . PHP_EOL);
        // }
        switch($server){
            case 'http':
                $className = \think\server\Http::class;
            break;
            case 'websocket':
                $className = \think\server\Websocket::class;
            break;
            case 'tcp':
                $className = \think\server\Tcp::class;
            break;
            case 'udp':
                $className = \think\server\Udp::class;
            break;
            default:
                if(isset($server['class_name']) && class_exists($server['class_name'])){
                    $className = $server['class_name'];
                }else{
                    echo 'server is not exist!' . PHP_EOL;
                }
        }
        new $className($config);
        
    }

    /**
     * 结束框架
     */
    public static function stop($config,$server)
    {
        if($config['pid_file']){
            $pid = @file_get_contents($config['pid_file']);
            if($pid){
                if(swoole_process::kill($pid, 0)){
                    swoole_process::kill($pid, SIGTERM);
                }else{
                    foreach(glob(RUNTIME_PATH . '*.pid') as $filename){
                        $pid = @file_get_contents($filename);
                        swoole_process::kill($pid, 0) && swoole_process::kill($pid, 9);
                        @unlink($filename);
                    }
                }
                die('Framework Stop of  Success!' . PHP_EOL);
            }
        }
        die('Framework not started!' . PHP_EOL);
    }

    /**
     * 框架运行状态
     */
    public static function status($config,$server)
    {
        if(isset($config['pid_file']) && $pid = @file_get_contents($config['pid_file'])){
            if($rs = \swoole_process::kill($pid, 0)){
                echo "Framework is running..", PHP_EOL, PHP_EOL;
                swoole_process::kill($pid, SIGSEGV);
                $i = 0;
                while(!file_exists(RUNTIME_PATH . 'status.info')){
                    usleep(100000);
                    if(++ $i > 100){
                        die('无法获取进程状态!' . PHP_EOL);
                    }
                }
                echo @file_get_contents(RUNTIME_PATH . 'status.info') . PHP_EOL;
                @unlink(RUNTIME_PATH . 'status.info');
                exit;
            }
        }
        die("Framework not started!" . PHP_EOL);
    }

    /**
     * 重启框架
     */
    public static function restart($config,$server)
    {
        if(isset($config['pid_file']) && $pid = @file_get_contents($config['pid_file'])){
            swoole_process::kill($pid, 0) || swoole_process::kill($pid, SIGTERM);
            foreach(glob(RUNTIME_PATH . '*.pid') as $filename){
                $pid = @file_get_contents($filename);
                swoole_process::kill($pid, 0) || swoole_process::kill($pid, 9);
                @unlink($filename);
            }
            echo('Stop of Framework Success!' . PHP_EOL);
        }else {
            echo('Framework not started!' . PHP_EOL);
        }
        sleep(1);
        self::start($config,$server);
    }

    /**
     * 重载(热重启)框架
     */
    public static function reload($config,$server)
    {
        if(isset($config['pid_file']) && $pid = @file_get_contents($config['pid_file'])){
            swoole_process::kill($pid, SIGUSR1);
            die("Reload signal has been issued!" . PHP_EOL);
        }
        die("Framework not started!" . PHP_EOL);
    }
}
