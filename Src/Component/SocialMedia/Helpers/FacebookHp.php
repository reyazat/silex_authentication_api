<?php

namespace Component\SocialMedia\Helpers;

use Component\SocialMedia\Assets\ConstantSocial;




use Component\SocialMedia\Helpers\SocialHp;

class FacebookHp extends ConstantSocial{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	private function connectFacebook(){
		
		$fb = new \Facebook\Facebook([
	  		'app_id' => self::facebook_app_id, // Replace {app-id} with your app id
	  		'app_secret' => self::facebook_app_secret,
	  		'default_graph_version' => self::facebook_default_graph_version,
	    ]);
		
		return $fb;
		
	}
	
	public function facebookLoginLink(){
		
		$fb = self::connectFacebook();
		$helper = $fb->getRedirectLoginHelper();

		$permissions = self::facebook_scope; // Optional permissions
		$loginUrl = $helper->getLoginUrl($this->facebook_redirect_url(), $permissions);
		
		return $loginUrl;
		
	}
	
	public function getFaceBookResult($request){
		
		session_start();
		$_SESSION['FBRLH_state'] = $request->get('state');
		$_SESSION['FBRLH_code'] = $request->get('code');
		
		$fb = self::connectFacebook();
		$helper = $fb->getRedirectLoginHelper();

		try {
		  $accessToken = $helper->getAccessToken();
			
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  
			// When Graph returns an error
			$this->app['monolog.debug']->warning('Graph returned an error: ' . $e->getMessage());
			$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
					
			return ['status'=>'error','message'=>$msg];

		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			
		  // When validation fails or other local issues
		  $this->app['monolog.debug']->warning('Facebook SDK returned an error: ' . $e->getMessage());
		  $msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
		  return ['status'=>'error','message'=>$msg];

		}

		if (! isset($accessToken)) {
			
		  if ($helper->getError()) {
			  
			  return ['status'=>'error','status code'=>401,'message'=>'Error Reason: '.$helper->getErrorReason().' Error Description:'.$helper->getErrorDescription()];

		  } else {
			  
			  return ['status'=>'error','status code'=>400,'message'=>'Bad request'];

		  }

		}

		// Logged in
		$accessTokenValue = '';
		$accessTokenValue = $accessToken->getValue();

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId(self::facebook_app_id); 
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		if (! $accessToken->isLongLived()) {
		  // Exchanges a short-lived access token for a long-lived one
		  try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		  } catch (Facebook\Exceptions\FacebookSDKException $e) {
			  
			  return ['status'=>'error','message'=>"Error getting long-lived access token: " . $helper->getMessage()];

		  }

		  $accessTokenValue = $accessToken->getValue();
			
		}
		
 		$getUserInfo = $fb->get('/me?fields='.self::facebook_fields, $accessTokenValue);
		$userInfo = $this->app['helper']('Utility')->decodeJson($getUserInfo->getBody());

		$userDetails = [];
		$userDetails = self::userInfo($userInfo);
		$SocialHp = new SocialHp($this->app);
		return $SocialHp->signInSocialUser($userDetails,'facebook');
		
	}
	
	private function userInfo($userInfo){
		
		$details = [];
		$details['first_name'] = $userInfo['first_name'];
		$details['last_name'] = $userInfo['last_name'];
		$details['email'] = $userInfo['email'];
		//$details['username'] = $userInfo['email'];
		$details['birthday'] = $this->app['helper']('DateTimeFunc')->changeDateFormat($userInfo['birthday'],'database');
		$details['gender'] = $userInfo['gender'];
		$details['address1'] = $userInfo['location']['name'];
		$details['signup_source'] = 'Facebook';
		$details['password'] = $this->app['helper']('CryptoGraphy')->randomPassword(10);
		
		return $details;
		
	}
	
	public function facebook_redirect_url(){
		
		return $this->app['baseUrl'].'socialoauth/facebook';
		
	}
	
}
