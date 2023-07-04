<?php

namespace Component\oAuth\Helpers;

use Component\oAuth\Models\SoftwareUser;

class SignUpHp{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function signUpUser($request){
		$SoftwareUser = new SoftwareUser($this->app);
		$res = $SoftwareUser->insertUser($request);
		return $res;

	}
	
}
