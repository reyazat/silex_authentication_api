<?php

namespace Component\oAuth\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\oAuth\Helpers\CompanyHp;



class Company implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];
		
		$CompanyHp =  new CompanyHp($this->app);
		
		$controllers->get(
			 '/{idcompany}',
			 function($idcompany) use($CompanyHp){
				
				$payLoad = $CompanyHp->companyInfo($idcompany);
				return $this->app->json($payLoad);
			
			 }
		);
		
		$controllers->get(
			 '/apikey/{apikey}',
			 function($apikey) use($CompanyHp){
				
				$payLoad = $CompanyHp->companyInfoByApiKey($apikey);
				return $this->app->json($payLoad);
			
			 }
		);
		
		$controllers->get(
			 '/',
			 function(Request $request) use($CompanyHp){
			
				$case = $request->get('action');
				switch($case){
						
					case'list' :
						$idUser = $request->get('id_user');
						$payLoad = $CompanyHp->userCompanyList($idUser);
					break;
						
					case'all' :
						$payLoad = $this->app['component']('oAuth_Models_CompanyDetails')->allCompany();
					break;

					case'freecheckout' :
						$payLoad = $this->app['component']('oAuth_Models_CompanyDetails')->freeCheckoutCompany();
					break;
						
					default:
						$message = 'AccessDenied.';
						return new Response($message, 401 , array('X-Status-Code' => 200));
					break;
						
				}				
			
				return $this->app->json($payLoad);
			
			 }
		);

        return $controllers;
    }

	
}

