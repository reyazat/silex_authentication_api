<?php

namespace Component\SocialMedia\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\SocialMedia\Helpers\FacebookHp;
use Component\SocialMedia\Helpers\GoogleHp;
use Component\SocialMedia\Helpers\LinkedinHp;

use Component\SocialMedia\Helpers\SocialHp;


class Social implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
		
        $controllers = $this->app['controllers_factory'];
		$SocialHp = new SocialHp($this->app);
		
		$controllers->get(
            '/link',
			function() use($SocialHp){
				
				$payLoad = [];
				$payLoad = $SocialHp->getSocialLoginLink();
			
				return $this->app->json($payLoad);
			
			}
			
        );
		
		$controllers->get(
            '/facebook',
			function(Request $request) use($SocialHp){
				$FacebookHp = new FacebookHp($this->app);
				$payLoad = [];
				$payLoad = $FacebookHp->getFaceBookResult($request);
			
				$res = $SocialHp->loginResult($payLoad);
				return $res;
			
			}
			
        );
		
		$controllers->get(
            '/google',
			function(Request $request) use($SocialHp){
				$GoogleHp = new GoogleHp($this->app);
				$payLoad = [];
				$payLoad = $GoogleHp->getGoogleResult($request);
			
				$res = $SocialHp->loginResult($payLoad);
				return $res;
			
			}
			
        );
		
		$controllers->get(
            '/linkedin',
			function(Request $request) use($SocialHp){
				$LinkedinHp = new LinkedinHp($this->app);
				$payLoad = [];
				$payLoad = $LinkedinHp->getLinkedinResult($request);
			
				$res = $SocialHp->loginResult($payLoad);
				return $res;
			
			}
			
        );

        return $controllers;
    }

	
}

