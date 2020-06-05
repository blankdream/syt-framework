<?php
namespace app;

use Yaf\{Application, Bootstrap_Abstract, Dispatcher, Loader, Registry};
//use app\Plugins\Sample;
class Bootstrap extends Bootstrap_Abstract{

    public function _initConfig(Dispatcher $dispatcher) {
		//关闭视图渲染
		$dispatcher->disableView();
		
		//把配置保存起来
		$arrConfig = Application::app()->getConfig();
		Registry::set('config', $arrConfig);
		//print_r(Application::app()->getConfig()->router->notHttp);die;
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
