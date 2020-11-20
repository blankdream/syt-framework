<?php

namespace app\controllers;
use think\Controller;
class Index extends Controller {

	public function index($name = "Stranger") {
	
		//$get = $this->getRequest()->getQuery("get", "default value");

		echo '89'.PHP_EOL.$name;
	}
	public function t() {
	
		//$get = $this->getRequest()->getQuery("get", "default value");

		echo '8888889999'.PHP_EOL;
	}
}
