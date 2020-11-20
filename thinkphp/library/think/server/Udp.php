<?php

namespace think\server;

use Yaf\{
    Registry,
    Loader,
    Application
};
use think\App;

class SwooleServer
{
    protected $_server;
    protected $_config;
    private $yaf_application;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_server = new \Swoole\Server($config['ip'], $config['port'], $config['mode'] ?? SWOOLE_PROCESS, $config['sock_type'] ?? SWOOLE_SOCK_UDP);
        $this->_server->on('Packet', [$this, 'onPacket']);
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        $this->_server->on('workerStop', [$this, 'onWorkerStop']);
        $this->_server->on('start', [$this, 'onStart']);
        $this->_server->set($config['settings']);

        foreach ($config['callbacks'] as $eventKey => $callbackItem) {
            [$class, $func] = $callbackItem;
            $this->_server->on($eventKey, [$class, $func]);
        }
        $this->_server->start();
    }

    public function onStart(\Swoole\Server $server)
    {
        echo "Swoole Server running：udp://{$this->_config['ip']}:{$this->_config['port']}\n";
    }

    public function onWorkerStart(\Swoole\Server $server, int $workerId)
    {
        //cli_set_process_title('swoole_worker_' . $work_id); // 设置worker子进程名称
        //yaf\Registry::set('swoole_serv', $serv);
        //define("APP_PATH",  realpath(dirname(__FILE__))); /* 指向public的上一级 */
        //Loader::import(APP_PATH . '/vendor/autoload.php');
        //require './vendor/autoload.php';
        //Loader::registerLocalNamespace('App\Plugins')
        //重命名进程名字
        if ($server->taskworker) {
            swoole_set_process_name('swooleTaskProcess');
        } else {
            swoole_set_process_name('swooleWorkerProcess');
        }
        try {
            $this->yaf_application = (new App())->get('yaf\app');
            $this->yaf_application->bootstrap();

            //$this->app->bootstrap();
        } catch (Throwable $e) {
            var_dump($e->getMessage());
        }

        //Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);
    }


    /**
     * worker/tasker进程结束的时候调用
     * 在此函数中可以回收worker进程申请的各类资源
     * @param \Swoole\Server $serv
     * @param int $work_id
     * @return bool
     */
    public function onWorkerStop(\Swoole\Server $server, int $workerId)
    {
    }

    public function onPacket(\Swoole\Server $server, int $fd, int $reactor_id, $data)
    {
    }
}
