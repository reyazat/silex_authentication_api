<?php
namespace Helper;

use \Silex\Application;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SymfonyPsrBridge {
	protected $app;
	
	public function __construct(Application $app){
		$this->app = $app;
    }
	public function psr2symfony($psrRequest = NULL,$psrResponse = NULL){
		
		$symfonyRequest = $symfonyResponse = '';
		
		$httpFoundationFactory = new HttpFoundationFactory();
		
		if($psrRequest !== NULL ){
			// convert a Request
			// $psrRequest is an instance of Psr\Http\Message\ServerRequestInterface
			$symfonyRequest = $httpFoundationFactory->createRequest($psrRequest);
		}

		if($psrResponse  !== NULL ){
			// convert a Response
			// $psrResponse is an instance of Psr\Http\Message\ResponseInterface
			$symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);
		}
		
		return array('request'=>$symfonyRequest,'response'=>$symfonyResponse);
	
	}
	
	public function symfony2psr($request = ''){
		
		// convert symfony request and resonse to psr7 
		$psr7Factory = new DiactorosFactory();

		// convert a Request
		$symfonyRequest = ($this->app['helper']('Utility')->notEmpty($request))?$request:Request::createFromGlobals();
		
		$psrRequest = $psr7Factory->createRequest($symfonyRequest);

		// convert a Response
		$symfonyResponse = new Response('Content');
		$psrResponse = $psr7Factory->createResponse($symfonyResponse);

		return array('request'=>$psrRequest,'response'=>$psrResponse);
		
	}
	
}


?>