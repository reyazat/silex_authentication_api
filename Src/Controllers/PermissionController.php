<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;


class PermissionController implements ControllerProviderInterface 
	
{	/**
     * Application
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){ 
		
		$this->app = $app;
		
		$index = $app['controllers_factory'];
		
		$index->get("/{companyId}",[$this, 'getPermissions']);
		$index->delete("/{plansId}",[$this, 'deletePermissions']);
		$index->get("/access/all",[$this, 'getAllAccess']);
		$index->post("/save",[$this, 'savePermissions']);
		$index->put("/{accessId}",[$this, 'updatePermissions']);
		
		return $index;
		
	}

	
	public function getAllAccess(Request $request) {
		
		$res = $this->app['helper']('Permission_PermissionHp')->getAllAccess($request);
		return new JsonResponse($res);
		
	}
	public function getPermissions(Request $request , $companyId) {
		
		$res = $this->app['helper']('Permission_PermissionHp')->getPermissions($request , $companyId);
		return new JsonResponse($res);
		
	}
	
	public function deletePermissions(Request $request , $plansId) {
		
		$res = $this->app['helper']('Permission_PermissionHp')->deletePermissions($request , $plansId);
		return new JsonResponse($res);
		
	}
 
	public function savePermissions(Request $request) {
		
		$res = $this->app['helper']('Permission_PermissionHp')->savePermissions($request);
		return new JsonResponse($res);
	}
 
	public function updatePermissions(Request $request , $accessId) {
		
		$res = $this->app['helper']('Permission_PermissionHp')->updatePermissions($request , $accessId);
		return new JsonResponse($res);
	}
	
}
