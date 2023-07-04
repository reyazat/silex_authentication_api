<?php

namespace Component\oAuth\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\oAuth\Models\SecurityQuestion;

use Component\oAuth\Helpers\ForgetPassHp;


class ForgetPass implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];
		  
		$ForgetPassHp = new ForgetPassHp($this->app);
		
		$controllers->get(
            '/securityquestion',
			function(Request $request){
				$SecurityQuestion = new SecurityQuestion($this->app);
				$res = $SecurityQuestion->getSecurityQuestion();
				return $this->app->json($res);
			
			}
			
        );
		
		$controllers->delete(
            '/code',
			function(Request $request) use($ForgetPassHp){
				
				$email = $request->get('email');
				$res = $ForgetPassHp->removeCodes($email);
				return $this->app->json($res);
			
			}
			
        );
		
		$controllers->post(
            '/email',
			function(Request $request) use($ForgetPassHp){
			
				$idUser = $request->get('id_user');
				$code = $request->get('code');
			
				$res = $ForgetPassHp->sendForgetPassMail($idUser,$code);
				return $this->app->json($res);
			
			}
			
        );
		
		$controllers->post(
            '/request',
			function(Request $request) use($ForgetPassHp){
			
				$code = $request->get('code');
				$res = $ForgetPassHp->checkRequest($code);
			
				return $this->app->json($res);
			
			}
		);
		
		
		$controllers->get(
            '/check',
			function(Request $request) use($ForgetPassHp){
			
				$email = $request->get('email');
				$res = $ForgetPassHp->checkWay($email);
				
				return $this->app->json($res);
			
			}
		);


        return $controllers;
    }

	
}

