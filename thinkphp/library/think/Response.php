<?php

namespace think;
class Response extends \Yaf\Controller_Abstract {

 	public static function __make(\Swoole\Http\Response $response){
 		return $response;
 	}
}
