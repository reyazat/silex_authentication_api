<?php
namespace Helper;

use Illuminate\Database\Capsule\Manager as Capsule;

class ModelLog{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function Log(){

		Capsule::listen(function($sql) {
		
			if($this->app){
				
				$this->app['monolog.sql']->debug('sql query',['sql'=>$sql->sql,
														  'binding'=>$sql->bindings,
														  'time'=>$sql->time]);
				
			}
			
        });
		
		
		return true;
	}
	
}