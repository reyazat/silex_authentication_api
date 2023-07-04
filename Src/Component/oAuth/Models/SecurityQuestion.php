<?php
namespace Component\oAuth\Models;

class SecurityQuestion extends \Illuminate\Database\Eloquent\Model{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
		
    }
	protected $table = 'sequrity_question';
	
	public function getSecurityQuestion(){
		$this->app['helper']('ModelLog')->Log();
		$questions = SecurityQuestion::select('question')->get()->toArray();
		$questionArr = $this->app['helper']('ArrayFunc')->getSpeceficKey($questions,'question','array');
		
		return $questionArr;
		
	}

	
}