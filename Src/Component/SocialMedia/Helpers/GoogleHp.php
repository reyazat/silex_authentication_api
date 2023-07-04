<?php

namespace Component\SocialMedia\Helpers;

use Component\SocialMedia\Assets\ConstantSocial;



use Component\SocialMedia\Helpers\SocialHp;

class GoogleHp extends ConstantSocial{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	private function connectGoogle(){
		
		$client = new \Google_Client();
		$client->setClientId(self::google_client_id);
		$client->setClientSecret(self::google_client_secret);
		
		$client->setRedirectUri($this->google_redirect_uri());
	
		foreach(self::google_scope as $scope){
			
			$client->addScope($scope);
			
		}
		
		return $client;
	}
	
	public function googleLoginLink(){
		
		$client = self::connectGoogle();
		return $client->createAuthUrl();
		
	}
	
	public function getGoogleResult($request){
		
		$client = self::connectGoogle();
		$service = new \Google_Service_Oauth2($client);
		
		if (isset($_GET['code'])) {
		  $client->authenticate($_GET['code']);
		  $accessToken = $client->getAccessToken();
		  $client->setAccessToken($accessToken);
		  //header('Location: ' . filter_var($this->google_redirect_uri(), FILTER_SANITIZE_URL));
		 // exit;
		}
		
		$user = $service->userinfo->get(); //get user info 
		$userDetails = self::userInfo($user);
		$SocialHp = new SocialHp($this->app);
		return $SocialHp->signInSocialUser($userDetails,'google');
		
	}
	
	private function userInfo($userInfo){
		
		$details = [];
		$details['first_name'] = $userInfo['givenName'];
		$details['last_name'] = $userInfo['familyName'];
		$details['email'] = $userInfo['email'];
		//$details['username'] = $userInfo['email'];
		$details['gender'] = $userInfo['gender'];
		$details['signup_source'] = 'Google';
		$details['password'] = $this->app['helper']('CryptoGraphy')->randomPassword(10);
		
		return $details;
		
	}

	public function google_redirect_uri(){
		
		return $this->app['baseUrl'].'socialoauth/google';
		
	}
}
