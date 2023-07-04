<?php
namespace Helper;

//use Component\oAuth\Helpers\UserInfo;

class ThemeRender{
	
	protected $app;
	public function __construct($app){
		
		$this->app = $app; 
		
	}
	
	public function render($body = '',$layout = 'default'){

		return $this->app['twig']->render('Layout/'.$layout.'.phtml', array('body'=>$body));
		
	}
	
}