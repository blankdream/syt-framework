<?php
//declare (strict_types = 1);

namespace think;

use Yaf\{
    Registry,
    Loader,
    Application,
};


class App extends Container
{
    
    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath = '';

    /**
     * 框架目录
     * @var string
     */
    protected $thinkPath = '';

    /**
     * 应用目录
     * @var string
     */
    protected $appPath = '';

    /**
     * Runtime目录
     * @var string
     */
    protected $runtimePath = '';

    /**
     * Yaf_Application实例
     * @var string
     */
    protected $application;

    public static $app;


    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;

    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [
        'app'       => App::class,
        'cache'     => Cache::class,
        'config'    => Config::class,
        'env'       => Env::class,
        'request'   => Request::class,
        'response' => Response::class
    ];

    /**
     * 架构方法
     * @access public
     * @param string $rootPath 应用根目录
     */
    public function __construct($rootPath='')
    {
        $this->rootPath = $rootPath ? rtrim($rootPath, DIRECTORY_SEPARATOR) : $this->getDefaultRootPath();
        //$this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        //$this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        $yafApp = new Application($this->loadConfig());
        static::setInstance($this);
        $this->instance('yaf\app', $yafApp);
        $this->instance('app', $this);
        $this->instance('think\Container', $this);
        
    }
    
    /**
     * 初始化应用
     * @access public
     * @return $this
     */
    public function initialize()
    {
        $this->initialized = true;

        // 加载全局初始化文件
        $this->load();

        return $this;
    }

    /**
     * 是否初始化过
     * @return bool
     */
    public function initialized()
    {
        return $this->initialized;
    }


    /**
     * 引导应用
     * @access public
     * @return void
     */
    public function boot(): void
    {
        array_walk($this->services, function ($service) {
            $this->bootService($service);
        });
    }

    /**
     * 加载应用配置文件
     * @access protected
     * @return array
     */
    public function loadConfig(): array
    {
        // 自动读取配置文件
        $dir = $this->rootPath .'/config';
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
        // 加载环境变量
        if (is_file($this->rootPath . '/.env')) {
            $env=$this->env->load($this->rootPath . '/.env', true);
            foreach($env as $k=>$v){
                if(is_array($v) && isset($config[$k])){
                    $config[$k]=array_merge($config[$k],$v);
                }else{
                    $config=array_merge($config,[$k=>$v]);
                }
            }
        }
        return $config;
    }


    /**
     * 是否运行在命令行下
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    protected function getDefaultRootPath(): string
    {
        return dirname(dirname(dirname(__DIR__)));
    }

}
