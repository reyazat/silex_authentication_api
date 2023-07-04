<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;


class PricingController implements ControllerProviderInterface 
	
{	/**
     * Application
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){ 
		
		$this->app = $app;
		
		$index = $app['controllers_factory'];
		
		$index->get("/plans/{serviceId}",[$this, 'getPlans']);
		$index->post("/plans/{serviceId}",[$this, 'savePlans']);
		$index->put("/plans/{serviceId}/{plansId}",[$this, 'updatePlans']);
		$index->delete("/plans/{serviceId}/{plansId}",[$this, 'deletePlans']);
		
		return $index;
		
	}

	
	public function getPlans(Request $request , $serviceId) {
		
		$res = $this->app['helper']('Pricing_PlansHp')->getPlans($serviceId , $request);
		return new JsonResponse($res);
		
	}
	
	
	public function deletePlans(Request $request , $serviceId , $plansId) {
		
		$res = $this->app['helper']('Pricing_PlansHp')->deletePlans($request , $serviceId , $plansId);
		return new JsonResponse($res);
		
	}
 
	public function savePlans(Request $request , $serviceId) {
		
		$res = $this->app['helper']('Pricing_PlansHp')->savePlans($request , $serviceId);
		return new JsonResponse($res);
	}
 
	public function updatePlans(Request $request , $serviceId , $plansId) {
		
		$res = $this->app['helper']('Pricing_PlansHp')->updatePlans($request , $serviceId , $plansId);
		return new JsonResponse($res);
	}
 
	
}
