<?php
namespace Helper;

class RequestParameter{

	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function getParameter($request = []){
		
		if($this->app['helper']('Utility')->notEmpty($request)){
			return $request->query->all();
		}else{
			return $this->app['request_content']->query->all();
		}
		
	}
	
	public function postParameter($request = []){
		
		if($this->app['helper']('Utility')->notEmpty($request)){
			return $request->request->all();
		}else{
			return $this->app['request_content']->request->all();
		}
				
	}
	
}