<?php

namespace Component\oAuth\Helpers;


use Component\oAuth\Models\OauthClient;

class SignInHp{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function signInUser($request){

		$postData = [];
		if(null !== $request->get('webLogin') && $request->get('webLogin') == 1){
			$OauthClient = new OauthClient($this->app);
			$findClient = $OauthClient->getClientByName('smarty');
			
			$postData['client_id'] = $findClient['client_id'];
			$postData['client_secret'] = $findClient['client_secret'];
			
		}else{
			
			$postData['client_id'] = $request->get('client_id');
			$postData['client_secret'] = $request->get('client_secret');
			
		}
		$postData['username'] = $request->get('username');
		$postData['password'] = $request->get('password');
		
		$signInUser = $this->app['helper']('HandlleRequest')->returnResult('/authenticate/accesstoken','POST',$postData);
		return $this->app['helper']('Utility')->convertResponseToArray($signInUser);

	}
	
}
