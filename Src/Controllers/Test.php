<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Silex\Api\ControllerProviderInterface;

use Symfony\Component\Finder\Finder;

class Test implements ControllerProviderInterface 
	
{	/**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){
		
		$this->app = $app;
		
		$index = $app['controllers_factory'];
		
		$index->get("/test",[$this, 'feedback']);
		
		return $index;
		
	}

	
	public function feedback(Request $request) {
		
		return $this->app['twig']->render('hami.phtml');
		
	}
 
	
}
