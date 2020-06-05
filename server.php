<?php

use Yaf\{
    Registry,
    Loader,
    Application,
};

class HttpServer
{
    public static $instance;
    public $http;
    public static $get;
    public static $post;
    public static $header;
    public static $server;
    private $application;

    private function __construct()
    {
        $http = new swoole_http_server("127.0.0.1", 9502);

        // $http->set([
        //     'worker_num' => 1,
        //     'daemonize' => true,
        //     'max_request' => 10000
        // ]);
        $http->on("start", function ($server) {
            echo "Swoole http server is started at http://127.0.0.1:9502\n";
        });
        $http->on('WorkerStart', [$this, 'onWorkerStart']);
        $http->on('request', [$this, 'onRequest']);
        $http->start();
    }

    public function onWorkerStart($serv, $work_id)
    {
        //cli_set_process_title('swoole_worker_' . $work_id); // 设置worker子进程名称
        //yaf\Registry::set('swoole_serv', $serv);
        //define("APP_PATH",  realpath(dirname(__FILE__))); /* 指向public的上一级 */
        //Loader::import(APP_PATH . '/vendor/autoload.php');
        //require './vendor/autoload.php';
        //Loader::registerLocalNamespace('App\Plugins')
        try {

            define('ROOT_PATH',  realpath(dirname(__FILE__)));

            // 自动读取配置文件
            $dir = ROOT_PATH .'/config';
            $files = is_dir( $dir) ? scandir($dir) : [];
            $config=[];
            foreach ($files as $file) {
                $nameExt = explode('.' , $file);
                if (isset($nameExt[1]) && 'php' == $nameExt[1]) {
                    if(isset($config[$nameExt[0]])){
                        array_merge($config[$nameExt[0]], include $dir . '/' . $file);
                    }else{
                        $config[$nameExt[0]] = include $dir . '/' . $file;
                    }
                }
            }
            //加载自定义函数
            Loader::import(ROOT_PATH . '/app/functions.php');

            $this->application = new Application($config);

            $this->application->bootstrap();
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function onRequest($request, $response)
    {
        //请求过滤,会请求2次
        if ('/favicon.ico' == $request->server['path_info'] || '/favicon.ico' == $request->server['request_uri']) {
            $response->end();
            return;
        }
        if (isset($request->server)) {
            HttpServer::$server = $request->server;
            foreach ($request->server as $k => $v) {
                $_SERVER[$k] = $v;
            }
        } else {
            HttpServer::$server = [];
        }
        if (isset($request->header)) {
            HttpServer::$header = $request->header;
            foreach ($request->header as $k => $v) {
                $_SERVER['HTTP_' . strtoupper($k)] = $v;
            }
        } else {
            HttpServer::$header = [];
        }
        if (isset($request->get)) {
            HttpServer::$get = $request->get;
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        } else {
            HttpServer::$get = [];
        }
        if (isset($request->post)) {
            HttpServer::$post = $request->post;
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        } else {
            HttpServer::$post = [];
        }
        $arr = ['SWOOLE_HTTP_REQUEST', 'SWOOLE_HTTP_RESPONSE'];
        foreach ($arr as $v) {
            if (Registry::has($v)) {
                Registry::del($v);
            }
        }
        // TODO handle img
        Registry::set('SWOOLE_HTTP_REQUEST', $request);
        Registry::set('SWOOLE_HTTP_RESPONSE', $response);

        ob_start();
        try {
            $yaf_request = new Yaf\Request\Http(HttpServer::$server['request_uri']);
            $this->application->getDispatcher()->dispatch($yaf_request);
        } catch (Yaf\Exception $e) {
            var_dump($e->getMessage().PHP_EOL.'88888888');
        }
        $result = ob_get_contents();

        ob_end_clean();
        $response->end($result);
        Registry::del('SWOOLE_HTTP_REQUEST');
        Registry::del('SWOOLE_HTTP_RESPONSE');
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
    }
}

HttpServer::getInstance();
