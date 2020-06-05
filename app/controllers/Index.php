<?php

namespace app\controllers;
use Yaf\Controller_Abstract;
class Index extends Controller_Abstract {

	public function index($name = "Stranger") {
	
		//$get = $this->getRequest()->getQuery("get", "default value");

		echo '89'.PHP_EOL;
	}
	public function t() {
	
		//$get = $this->getRequest()->getQuery("get", "default value");

		echo '8888'.PHP_EOL;
	}
}
