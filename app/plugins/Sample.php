<?php

namespace app\Plugins;

use Yaf\{
    Registry,
    Plugin_Abstract,
    Request_Abstract,
    Response_Abstract,
};
class Sample extends Plugin_Abstract {

	public function routerStartup(Request_Abstract $request, Response_Abstract $response) {
	}

	public function routerShutdown(Request_Abstract $request, Response_Abstract $response) {
	}

	public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response) {
	}

	public function preDispatch(Request_Abstract $request, Response_Abstract $response) {
	}

	public function postDispatch(Request_Abstract $request, Response_Abstract $response) {
	}

	public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response) {
	}
}
