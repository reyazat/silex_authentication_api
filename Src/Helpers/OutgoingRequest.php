<?php
namespace Helper;

use Symfony\Component\HttpFoundation\Request;


class OutgoingRequest{
	
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function checkAccessBeforeRequest(){
		
		return true;
		
	}
	
	public function postRequest( $url,$header = [],$data = [] ,$options = [] ,$checkToken = true ){

		if($checkToken === true){
			$checkAccess = $this->checkAccessBeforeRequest();
		}

		$makeRequest = \Requests::post($url, $header, $data,$options);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);	
		return $requestResult;
		
	}
	
	public function putRequest( $url,$header = [],$data = [] ,$options = [],$checkToken = true ){

		if($checkToken === true){
			$checkAccess = $this->checkAccessBeforeRequest();
		}

		$makeRequest = \Requests::put($url, $header, $data,$options);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);	
		return $requestResult;
		
	}
	public function patchRequest( $url,$header = [],$data = [] ,$options = [],$checkToken = true ){

		if($checkToken === true){
			$checkAccess = $this->checkAccessBeforeRequest();
		}

		$makeRequest = \Requests::patch($url, $header, $data,$options);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);	
		return $requestResult;
		
	}
	
	public function getRequest( $url,$header = [],$data = [] ,$options = [],$checkToken = true){

		if($checkToken === true){
			
			$checkAccess = $this->checkAccessBeforeRequest();
			
		}
		
		
		$queryString = http_build_query($data);
		
		$makeRequest = \Requests::get($url.'?'.$queryString, $header, $options);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);
		
		return $requestResult;
		
	}
	
	public function deleteRequest( $url,$header = [],$data = [] ,$options = [],$checkToken = true){

		if($checkToken === true){
			
			$checkAccess = $this->checkAccessBeforeRequest();
			
		}
		
		
		$queryString = http_build_query($data);
		
		$makeRequest = \Requests::delete($url.'?'.$queryString, $header, $options);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);
		
		return $requestResult;
		
	}

	
}