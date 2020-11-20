<?php

namespace think\server;

use think\App;

class Http
{
    protected $_server;
    protected $_config;
    private $yaf_application;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_server = new \Swoole\Http\Server($config['ip'], $config['port']);
        $this->_server->on('request', [$this, 'onRequest']);
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

    public function onStart(\Swoole\Http\Server $server)
    {
        echo "Swoole Server running：http://{$this->_config['ip']}:{$this->_config['port']}\n";
    }

    public function onWorkerStart(\Swoole\Http\Server $server, int $workerId)
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

        \Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);
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

        ob_start();
        try {
            //$req = new \think\Request($request);
            //$req->init($request);
            //$res = new \think\Response($response);
            //var_dump($response);
            
            $req = new \Yaf\Request\Http($request->server['request_uri']);
            $req->method = $request->server['request_method'];
            $res = $this->yaf_application->getDispatcher()->dispatch($req);
            //$res = \Yaf\Dispatcher::getInstance()->dispatch($req)->getBody();
            $result = ob_get_contents();
            ob_end_clean();
            //if ($this->http->connection_info($response->fd) !== false){
            $response->end($result);
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
    public function onWorkerStop(\Swoole\Http\Server $server, int $workerId)
    {
    }

    public function onManagerStart(\Swoole\Http\Server $server)
    {
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
    public function onReceive(Swoole\Http\Server $server, int $fd, int $reactor_id, $data): void
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
