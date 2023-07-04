<?php
namespace Helper;

use Symfony\Component\HttpFoundation\Request;


class OutgoingRequest{
	
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function checkAccessBeforeRequest(){
		
		return $this->app['check_access']($this->app['request_content']);
		
	}
	
	public function postRequest( $url,$header = [],$data = [] ,$checkToken = true ){

		/*if($checkToken === true){
			$checkAccess = self::checkAccessBeforeRequest();
		}*/

		$makeRequest = \Requests::post($url, $header, $data);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);	
		return $requestResult;
		
	}
	
	public function getRequest( $url,$header = [],$data = [] ,$checkToken = true){

		/*if($checkToken === true){
			
			$checkAccess = self::checkAccessBeforeRequest();
			
		}*/
		
		$queryString = http_build_query($data);
		
		$makeRequest = \Requests::get($url.'?'.$queryString, $header, []);
		$requestResult = $this->app['helper']('Utility')->decodeJson($makeRequest->body);
		
		return $requestResult;
		
	}

	
}