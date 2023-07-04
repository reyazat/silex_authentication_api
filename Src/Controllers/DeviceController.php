<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DeviceController
{	/**
 * Application
 *
 * @var Silex\Application
 */
    protected $app;
    public function __construct($app)
    {
        $this->app = $app;
    }


    public function addRoutes($routing){
				
        $routing->get("/device", [$this, 'deviceListController']);
		$routing->post("/device", [$this, 'addDeviceController']);
	    $routing->delete("/device/{device_token}", [$this, 'delDeviceController']);
    }
	
	public function deviceListController(Request $request){
		$credential = $request->headers->get('credential');
		$token = $request->headers->get('token');
			
		$payLoad = $this->app['helper']('DeviceController_DeviceHp')->deviceListHp($credential, $token);
		return setResponse($payLoad);
	}
	public function delDeviceController($device_token , Request $request){
		$credential = $request->headers->get('credential');
		$token = $request->headers->get('token');
			
		$payLoad = $this->app['helper']('DeviceController_DeviceHp')->delDeviceHp($device_token, $credential, $token);
		return setResponse($payLoad);
	}
	
	public function addDeviceController(Request $request){
		$credential = $request->headers->get('credential');
		$params = $this->app['helper']('RequestParameter')->postParameter();
			
		$payLoad = $this->app['helper']('DeviceController_DeviceHp')->addDeviceHp($credential, $params);
		return setResponse($payLoad);
	}
	
}