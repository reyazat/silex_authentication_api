<?php
use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


// The middleware is run before the routing and the security
$this->app->before(function (Request $request, Application $app) {
	
	if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = $this->app['helper']('Utility')->decodeJson($request->getContent(), true);
		if(is_array($data)){
			foreach($data as $ky=>$val){
				if(!is_array($val))$data[$ky] = $this->app['helper']('Utility')->secureInput($val);
			}
		}
        $request->request->replace(is_array($data) ? $data : array());
    }
	
	
	$this->app['request_content'] = $request;
	


	
	/* Set Language mode*/
	if(null!==$request->headers->get('content-language')){
		$local = $this->app['helper']('Utility')->trm($request->headers->get('content-language'));
		
		$request->setLocale($local);
	}

	
}, Application::EARLY_EVENT);


$this->app->before(function (Request $request, Application $app) {
	$authToken = $request->headers->get('token');
	$credential = $request->headers->get('credential');
	
	return $this->app['helper']('Permissions_Permissions')->init($authToken, $credential);
	
});


$this->app->before(function (Request $request, Application $app) {

}, Application::LATE_EVENT);


$this->app->after(function (Request $request, Response $response){

});


// Set event after the Response
$this->app->finish(function (Request $request, Response $response)  {
			
});
