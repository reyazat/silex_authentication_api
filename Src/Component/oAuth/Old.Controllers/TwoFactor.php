<?php
namespace Component\oAuth\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Component\oAuth\Helpers\TwoFactorHp;

class TwoFactor implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];
		 
		$controllers->post("/setup",[$this,'setupApp'])->bind('setupApp');
		
		$controllers->post("/verifyapp",[$this,'verifyApp'])->bind('verifyApp');
		$controllers->post("/verifycode",[$this,'verifyCode'])->bind('verifyCode');
		$controllers->post("/disableapp",[$this,'disableApp'])->bind('disableApp');
		$controllers->post("/disablebyquestion",[$this,'disableByQuestion']);
		$controllers->post("/verifybyquestion",[$this,'verifyByQuestion']);
		
		return $controllers;

	}
	
	public function setupApp(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->setUpApp($request);
		
		return $res;
	}
	
	
	public function verifyapp(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->verifyApp($request);
		
		return $res;
	}
	
	public function verifycode(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->verifyRequest($request);
		
		return $res;
	}
	
	public function disableApp(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->disableApp($request);
		return $res;
	}
	
	public function disableByQuestion(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->disableByQuestion($request);
		return $res;
	}
	
	public function verifyByQuestion(Request $request){
		$hp = new TwoFactorHp($this->app);
		$res = $hp->verifyByQuestion($request);
		return $res;
	}
}