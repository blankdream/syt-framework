<?php

namespace app\controllers;

use Yaf\Controller_Abstract;

class Error extends Controller_Abstract
{

	//从2.1开始, errorAction支持直接通过参数获取异常
	public function error($exception)
	{
		$exception = $this->getRequest()->getException();

		try {
			throw $exception;
		} catch (Yaf_Exception_LoadFailed $e) {

			print_r($exception->getMessage() . '55');
		} catch (Yaf_Exception $e) {

			print_r($exception->getMessage() . '666');
		}
	}
}
