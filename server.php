<?php

use Yaf\{
    Registry,
    Loader,
    Application
};

use think\App;
class HttpServer
{
    public static $instance;
    public $http;
    private $yaf_obj;

    private $config = array(
        'worker_num'    => 1,
        'daemonize'     => false,
        'max_request'   => 10000,
        'dispatch_mode' => 3, // 抢占模式
        //'log_file'      => '/wwwroot/share/swoole/yaf/log/server.log',
    );

    private function __construct()
    {
        $this->http = new swoole_http_server("127.0.0.1", 9502);

        $this->http->set($this->config);
        $this->bind($this->config);
        
    }

    public function onStart($serv){
        echo "Swoole http server is started at http://127.0.0.1:9502\n";
    }

    public function onWorkerStart($serv, $work_id)
    {
        //cli_set_process_title('swoole_worker_' . $work_id); // 设置worker子进程名称
        //yaf\Registry::set('swoole_serv', $serv);
        //define("APP_PATH",  realpath(dirname(__FILE__))); /* 指向public的上一级 */
        //Loader::import(APP_PATH . '/vendor/autoload.php');
        //require './vendor/autoload.php';
        //Loader::registerLocalNamespace('App\Plugins')
        //重命名进程名字
        if ($serv->taskworker) {
            swoole_set_process_name('swooleTaskProcess');
        } else {
            swoole_set_process_name('swooleWorkerProcess');
        }
        try {
            define('ROOT_PATH',  realpath(dirname(__FILE__)));
            require __DIR__ . '/thinkphp/base.php';
            $this->yaf_obj = (new App())->get('yaf\app')->bootstrap();
            //$this->yafApp->bootstrap();
         
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
    public function onRequest(Swoole\Http\Request $request, Swoole\Http\Response $response): void
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
        //注册全局信息
    
        // if (isset($request->server)) {
        //     HttpServer::$server = $request->server;
        //     foreach ($request->server as $k => $v) {
        //         $_SERVER[$k] = $v;
        //     }
        // } else {
        //     HttpServer::$server = [];
        // }
        // if (isset($request->header)) {
        //     HttpServer::$header = $request->header;
        //     foreach ($request->header as $k => $v) {
        //         $_SERVER['HTTP_' . strtoupper($k)] = $v;
        //     }
        // } else {
        //     HttpServer::$header = [];
        // }
        // if (isset($request->get)) {
        //     HttpServer::$get = $request->get;
        //     foreach ($request->get as $k => $v) {
        //         $_GET[$k] = $v;
        //     }
        // } else {
        //     HttpServer::$get = [];
        // }
        // if (isset($request->post)) {
        //     HttpServer::$post = $request->post;
        //     foreach ($request->post as $k => $v) {
        //         $_POST[$k] = $v;
        //     }
        // } else {
        //     HttpServer::$post = [];
        // }
        // $arr = ['SWOOLE_HTTP_REQUEST', 'SWOOLE_HTTP_RESPONSE'];
        // foreach ($arr as $v) {
        //     if (Registry::has($v)) {
        //         Registry::del($v);
        //     }
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
            
            $dispatch = $this->yaf_obj->getDispatcher();
            $dispatch->returnResponse(true);
            $res = $dispatch->dispatch($req);

           print_r($res);
            //$result = ob_get_contents();
            //ob_end_clean();
            //if ($this->http->connection_info($response->fd) !== false){
                $response->end($res->getBody().'success');
            //}
        } catch (\Yaf\Exception $e) {
            var_dump($e->getMessage().'888');
        }catch (Throwable $e) {
            var_dump($e->getMessage().'999'.$e->getFile().'---'.$e->getLine());
        }finally{
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
  public function onWorkerStop(\Swoole\Server $serv, int $work_id)
  {
    // 回收对应进程申请的资源
    if (is_resource($this->application))
    {
      $this->application = null;
    }
    

    return true;
  }


  // 绑定回调函数
  private function bind($config)
  {
    $this->http->on('Start', [$this, 'onStart']);
    //$this->http->on('Close', [$this, 'onClose']);
    //$this->http->on('Connect', [$this, 'onConnect']);
    $this->http->on('Request', [$this, 'onRequest']);
    //$this->http->on('Receive', [$this, 'onReceive']);
    //$this->http->on('Shutdown', [$this, 'onShutdown']);
    //$this->http->on('WorkerStop', [$this, 'onWorkerStop']);
    $this->http->on('WorkerStart', [$this, 'onWorkerStart']);
    //$this->http->on('WorkerError', [$this, 'onWorkerError']);
    //$this->http->on('ManagerStop', [$this, 'onManagerStop']);
    //$this->http->on('ManagerStart', [$this, 'onManagerStart']);
    if (isset($config['task_worker_num']) && boolval($config['task_worker_num']))
    {
      $this->http->on('Task', [$this, 'onTask']);
      $this->http->on('Finish', [$this, 'onFinish']);
    }

    return true;
  }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
    }
}

HttpServer::getInstance()->http->start();
