<?php

namespace app\api\controllers;

use Yaf\Controller_Abstract;

class Api extends Controller_Abstract
{

	public function index($name = "Stranger")
	{

		//$get = $this->getRequest()->getQuery("get", "default value");

		echo '89999999' . PHP_EOL;
	}
	public function t()
	{

		$c = new \app\models\Sample();
		echo tt($c->selectSample());
	}
}
