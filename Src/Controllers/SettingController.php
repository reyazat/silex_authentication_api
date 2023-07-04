<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;


class SettingController implements ControllerProviderInterface 
	
{	/**
     * Application
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){ 
		
		$this->app = $app;
		
		$index = $app['controllers_factory'];
		
		$index->get("/softwares",[$this, 'getSoftwares']);
		$index->post("/softwares",[$this, 'saveSoftwares']);
		$index->put("/softwares/{id}",[$this, 'updateSoftwares']);
		$index->delete("/softwares/{serviceId}",[$this, 'delSoftwares']);
		
		return $index;
		
	}

	
	
	public function getSoftwares(Request $request) {
		
		$res = $this->app['helper']('Setting_SettingHp')->getSoftwares($request);
		return new JsonResponse($res);
		
	}
	
	public function saveSoftwares(Request $request) {
		
		$res = $this->app['helper']('Setting_SettingHp')->saveSoftwares($request);
		return new JsonResponse($res);
		
	}
	public function updateSoftwares(Request $request , $id) {
		
		$res = $this->app['helper']('Setting_SettingHp')->updateSoftwares($request , $id);
		return new JsonResponse($res);
		
	}public function delSoftwares(Request $request , $serviceId) {
		
		$res = $this->app['helper']('Setting_SettingHp')->delSoftwares($request , $serviceId);
		return new JsonResponse($res);
		
	}
	
	
}
