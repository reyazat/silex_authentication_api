<?php
use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


// The middleware is run before the routing and the security
$this->app->before(function (Request $request, Application $app) {

	if(null!==$request->headers->get('content-language')){
		$local = $this->app['helper']('Utility')->trm($request->headers->get('content-language'));
		
		$request->setLocale($local);
	}

	$this->app['request_content'] = $request;									  
	//$this->app['helper']('HandlleRequest')->requestLimit();
	return $this->app['component']('oAuth_Helpers_CheckAccess')->run($request);
	
	
}, Application::EARLY_EVENT);


$this->app->before(function (Request $request, Application $app) {

});


$this->app->before(function (Request $request, Application $app) {

}, Application::LATE_EVENT);


$this->app->after(function (Request $request, Response $response){
	$mode = $request->get('view');
	if($mode == 'app'){
		$res = $response->getContent();
		$jsonDecode = json_decode($res, true);
		if(!isset($jsonDecode['status'])){
			return new JsonResponse(['status'=>'success','data'=>$jsonDecode]);
		}else if(isset($jsonDecode['status']) && ($jsonDecode['status'] != 'success' && $jsonDecode['status'] != 'error' && $jsonDecode['status'] != 'expire')){
			return new JsonResponse(['status'=>'success','data'=>$jsonDecode]);
		}else{
			return new JsonResponse($jsonDecode);
		}
	}
});


// Set event after the Response
$this->app->finish(function (Request $request, Response $response)  {
			
});
