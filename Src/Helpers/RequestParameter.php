<?php
namespace Helper;

class RequestParameter{

	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function getParameter($request = []){
		
		if($this->app['helper']('Utility')->notEmpty($request)){
			$res =  $request->query->all();
		}else{
			$res =  $this->app['request_content']->query->all();
		}
		if(is_array($res)){
			foreach($res as $ky=>$val){
				if(!is_array($val))$res[$ky] = $this->app['helper']('Utility')->secureInput($val);
			}
		}
		
		return $res;
	}
	
	public function postParameter($request = []){
		
		if($this->app['helper']('Utility')->notEmpty($request)){
			$res =   $request->request->all();
		}else{
			$res =   $this->app['request_content']->request->all();
		}
		if(is_array($res)){
			foreach($res as $ky=>$val){
				if(!is_array($val))$res[$ky] = $this->app['helper']('Utility')->secureInput($val);
			}
		}
		
		return $res;		
	}
	
}