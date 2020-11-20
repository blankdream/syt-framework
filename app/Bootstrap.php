<?php
namespace app;

use Yaf\{Application, Bootstrap_Abstract, Dispatcher, Loader, Registry};
//use app\Plugins\Sample;
class Bootstrap extends Bootstrap_Abstract{

    public function _initConfig(Dispatcher $dispatcher) {
		//开启视图渲染
		//$dispatcher->enableView();
		//把配置保存起来
		$config = Application::app()->getConfig();
		Registry::set('config', $config);
		
	}

	public function _initPlugin(Dispatcher $dispatcher) {
		//注册一个插件
		$objSamplePlugin = new \app\plugins\Sample();
		$dispatcher->registerPlugin($objSamplePlugin);
	}

	public function _initRoute(Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
	}

	public function _initView(Dispatcher $dispatcher){
		//在这里注册自己的view控制器，例如smarty,firekylin
	}
}
