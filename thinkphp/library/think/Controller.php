<?php

namespace think;
use Yaf\Controller_Abstract;
class Controller extends Controller_Abstract 
{
    public $app;
    public $request;
    public $response;
 	public function __construct(App $app = null)
    {
 		$this->app      = $app ?: App::get('app');
        $this->request  = $this->app['request'];
        $this->response = $this->app['response'];
    }
	protected function error($msg = '', $code = -1000, $data = '', array $header = [], $wait = 3)
    {
        $response = ['code' => $code, 'msg' => $msg];
        isset($data) && !empty($data) && $response['data'] = $data;
        echo json_encode($response);
        return true;
    }

    protected function success($msg = '', $data = '', $code = 0, array $header = [], $wait = 3)
    {
        $response = ['code' => $code, 'msg' => $msg];
        isset($data) && !empty($data) && $response['data'] = $data;
        echo json_encode($response);
        return true;
        //$this->response->end(json_encode($data));
    }
}
