<?php

namespace app\api\controllers;

use think\Controller;

class Api extends Controller
{

	public function index($name = "Stranger")
	{

		//$get = $this->getRequest()->getQuery("get", "default value");

		//$this->error('dfsfssfssf'.$name);
		//$this->getResponse()->setBody("Hello World");
		sleep(5);
		return $this->error('dfsfssfssf'.$name);
		
		//var_dump($this->getRequest());
		//var_dump($this->getResponse()->getBody());
		//return 'cfsdfsa';
		
	}

	public function t1()
	{
		sleep(5);
		print_r($this->getRequest());
		return $this->error('t11111111');
		//$this->getResponse()->setBody("Hello World999");
	}

	public function t2()
	{
		print_r($this->getRequest());
		return $this->error('t22222222');
		//$this->getResponse()->setBody("Hello World999");
	}
}
