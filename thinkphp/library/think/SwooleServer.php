<?php

namespace think;

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
        switch($config['type']){
            case 'http': 
                $this->_server = new \Swoole\Http\Server($config['ip'],$config['port']);
                $this->_server->on('request', [$this, 'onRequest']);
            break;
            case 'websocket': 
                $this->_server = new \Swoole\WebSocket($config['ip'],$config['port']);
                $this->_server->on('open', [$this, 'onOpen']);
                $this->_server->on('message', [$this, 'onMessage']);
                $this->_server->on('close', [$this, 'onClose']);
            case 'tcp': 
                $this->_server = new \Swoole\Server($config['ip'],$config['port']);
                $this->_server->on('connect', [$this, 'onConnect']);
                $this->_server->on('receive', [$this, 'onReceive']);
                $this->_server->on('close', [$this, 'onClose']);
            break;
            case 'udp': 
                $this->_server = new \Swoole\Server($config['ip'],$config['port'],SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
                $this->_server->on('Packet', [$this, 'onPacket']);
            break;
            default:
                $this->_server = new \Swoole\Server($config['ip'],$config['port'],$config['mode'],$config['sock_type']);
                $this->_server->on('connect', [$this, 'onConnect']);
                $this->_server->on('receive', [$this, 'onReceive']);
                $this->_server->on('close', [$this, 'onClose']);
        }
        
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        $this->_server->on('workerStop', [$this, 'onWorkerStop']);
        $this->_server->set($config['settings']);
        if ($config['mode'] == SWOOLE_BASE) {
            $this->_server->on('managerStart', [$this, 'onManagerStart']);
        } else {
            $this->_server->on('start', [$this, 'onStart']);
        }
        foreach ($config['callbacks'] as $eventKey => $callbackItem) {
            [$class, $func] = $callbackItem;
            $this->_server->on($eventKey, [$class, $func]);
        }
        $this->_server->start();
    }

    public function onStart(\Swoole\Server $server)
    {
        echo "Swoole Server running：http://{$this->_config['ip']}:{$this->_config['port']}\n";
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
     * http协议路由转接
     *
     * @param Swoole\Http\Request  $request
     * @param Swoole\Http\Response $response
     *
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
        //请求过滤,会请求2次
        if ('/favicon.ico' == $request->server['path_info'] || '/favicon.ico' == $request->server['request_uri']) {
            $response->end();
            return;
        }
        //print_r($this->app->get('app'));
        // $request_uri = explode('/', $request->server['request_uri']);
        // $yaf_config  = Registry::get('config');
        // //过滤掉固定的几个模块不能在外部http直接访问
        // $router = explode(',', $yaf_config['router']['notHttp']);
        // if (in_array($request_uri[1], $router)) {
        //     $response->status(404);
        //     $response->end();
        //     return;
        // }
        // //预检
        // if ($request->server['request_method'] == "OPTIONS") {
        //     $response->end();
        //     return;
        // }
        
        // $cid = Swoole\Coroutine::getCid();
        // Registry::set('request_' . $cid, $request);
        //Registry::set('response_' . $cid, $response);

        //ob_start();
        try {
            $req = new \think\Request($request);
            //$req->init($request);
            //$res = new \think\Response($response);
            var_dump($response);
            $req->method = $request->server['request_method'];

            $dispatch = $this->yaf_application->getDispatcher();
            $dispatch->returnResponse(true);
            $res = $dispatch->dispatch($req);

            print_r($res);
            //$result = ob_get_contents();
            //ob_end_clean();
            //if ($this->http->connection_info($response->fd) !== false){
            $response->end($res->getBody() . 'success');
            //}
        } catch (\Yaf\Exception $e) {
            var_dump($e->getMessage() . '888');
        } catch (Throwable $e) {
            var_dump($e->getMessage() . '999' . $e->getFile() . '---' . $e->getLine());
        } finally {
            //清理协程容器
            //$this->app->clear();
        }
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

    public function onManagerStart(\Swoole\Server $server)
    {
        
    }

    /**
     * Swoole正常关闭时回调
     * @param \Swoole\Server $server
     */
     public static function shutdown(\Swoole\Server $server)
    {
        //实例启动后执行
        foreach(glob(RUNTIME_PATH . '*.pid') as $filename){
            $pid = @file_get_contents($filename);
            if(\swoole_process::kill($pid, 0))\swoole_process::kill($pid, 9);
            @unlink($filename);
        }
    }

    /**
     * tcp协议路由转接
     *
     * @param Swoole\WebSocket\Server  $server
     * @param int                      $fd
     * @param int                      $reactor_id
     * @param                          $data
     */
    //todo 暂未实现路由Tcp模块
    public function onReceive(Swoole\WebSocket\Server $server, int $fd, int $reactor_id, $data): void
    {
        // $data      = substr($data, 4);
        // $res       = json_decode($data, true);
        // $res['fd'] = $fd;
        // $req_obj   = new Yaf\Request\Http($res['uri'], '/');
        // $req_obj->setParam($res);

        // try {
        //     $this->yaf_obj->getDispatcher()->dispatch($req_obj);
        // } catch (Throwable $e) {
        //     co_log(
        //         ['message' => $e->getMessage(), 'trace' => $e->getTrace()],
        //         'onReceive Throwable message:',
        //         'receive'
        //     );
        // }
    }
}
