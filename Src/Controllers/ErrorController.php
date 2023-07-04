<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;

class ErrorController implements ControllerProviderInterface 
	
{	/**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){
		$this->app = $app;
		
		$controllers = $app['controllers_factory'];
		
		$controllers->get(
            '/{errorcode}',
			function($errorcode){
			
				$template = $this->app['twig']->render('ErrorController/'.$errorcode.'.phtml');
				return $this->app['helper']('ThemeRender')->render($template,'error');
			
			}
			
        );
		
		return $controllers;
	}
	
	
	
}
