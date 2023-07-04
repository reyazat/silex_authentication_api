<?php

namespace Component\oAuth\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\oAuth\Helpers\AuthenticateHp;
use Component\oAuth\Helpers\SignUpHp;
use Component\oAuth\Helpers\SignInHp;

use Component\oAuth\Models\OauthClient;

class Authenticate implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];
		  
		$AuthenticateHp = new AuthenticateHp($this->app);
		
		$controllers->post(
            '/accesstoken',
			function(Request $request) use($AuthenticateHp){
				$request->request->set('grant_type','password');
				$payLoad = [];
				$payLoad = $AuthenticateHp->getAccessToken($request);
				
				return $this->app->json($payLoad);
			
			}
			
        );
		
        $controllers->post(
            '/signin',
			function(Request $request){
				$SignInHp = new SignInHp($this->app);
				$res = $SignInHp->signInUser($request);
				return $this->app->json($res);
				
			}
			
        );
		
		
		$controllers->post(
            '/signup',
			function(Request $request){
				$SignUpHp = new SignUpHp($this->app);
				$res = $SignUpHp->signUpUser($request);
				return $this->app->json($res);
			
			}
			
        );
		
		$controllers->post(
			 '/validate',
			 function(Request $request) use($AuthenticateHp){
			
				$payLoad = [];
				$payLoad = $AuthenticateHp->validateAccessToken($request);
				
				return $this->app->json($payLoad);
			
			}
		);
		
		$controllers->post(
			 '/refreshtoken',
			 function(Request $request) use($AuthenticateHp){

				$request->request->set('grant_type', 'refresh_token');
				if(null !==$request->get('webLogin') && $request->get('webLogin') == 1){
					$OauthClient = new OauthClient($this->app);
					$findClient = $OauthClient->getClientByName('smarty');
					
					$request->request->set('client_id', $findClient['client_id']);
					$request->request->set('client_secret', $findClient['client_secret']);

				}
			
				$payLoad = [];
				$payLoad = $AuthenticateHp->updateAccessToken($request);
				
				return $this->app->json($payLoad);
			
			}
		);
		
		

		$controllers->post(
			 '/recaptcha',
			 function(Request $request) use($AuthenticateHp){
			 	
				$response = $request->get('response');
				$payLoad = $AuthenticateHp->recaptcha($response);
			
				return $this->app->json($payLoad);	
			
			}
		);
		


        return $controllers;
    }

	
}

