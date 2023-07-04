<?php

namespace Component\SocialMedia\Helpers;

use Component\SocialMedia\Assets\ConstantSocial;

use Component\SocialMedia\Helpers\SocialHp;

class LinkedinHp extends ConstantSocial{

	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function linkedinLoginLink(){
		
		$linkedinUrl = '';
		$linkedinUrl = self::linkedin_authorization;
		$linkedinUrl .= '?response_type=code&client_id='.self::linkedin_client_id.'&scope='.implode('%20',self::linkedin_scope).'&redirect_uri='.$this->linkedin_redirect_uri();
		
		$linkedinUrl .= '&state='.self::linkedin_state();

		//$linkedinUrl .= '&scope='.implode('&',self::linkedin_scope);
		
		return $linkedinUrl;
		
	}
	
	public function getLinkedinResult($request){
		
		$state = $request->get('state');
		if(password_verify(self::linkedin_hash,$state) === false){

			// users not matched, maybe attack occurred
			//$this->app['monolog.debug']->warning('Error occurred in validate linkedin sign in.');

		}else{

			$error = $request->get('error');
			if($this->app['helper']('Utility')->notEmpty($error) && $error == 'user_cancelled_authorize'){

				// user cancel the authorization
				return ['status'=>'error','message'=>'Sign In canceled.'];

			}else{

				$code = $request->get('code');
				if(!$this->app['helper']('Utility')->notEmpty($code)){
					
					$this->app['monolog.debug']->error("can not get code from linked signIn");
					$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
					
					return ['status'=>'error','message'=>$msg];
					
				}else{
					
					$response = \Requests::post(self::linkedin_accesstoken_url, array(), ['grant_type'=>'authorization_code',
					 'code'=>$code,
					 'redirect_uri'=>$this->linkedin_redirect_uri(),
					 'client_id'=>self::linkedin_client_id,
					 'client_secret'=>self::linkedin_client_secret]);

					$responseBody = $response->body;
					$accessToken = $this->app['helper']('Utility')->decodeJson($responseBody)['access_token'];

					if($this->app['helper']('Utility')->notEmpty($accessToken)){

						$headers = array('Authorization' => 'Bearer '.$accessToken);
						
						$getProfileEmail = \Requests::get(self::linkedin_email_url, $headers);
						$profileEmailInfo = $getProfileEmail->body;
						
						$emailInfo = $this->app['helper']('Utility')->decodeJson($profileEmailInfo);
						$email = '';
						if(isset($emailInfo['elements'])){
							foreach($emailInfo['elements'] as $element){
								
								if((isset($element['type']) && $element['type'] == 'EMAIL') && 
								   (isset($element['primary']) && $element['primary'] === true)){
									
									if(isset($element['handle~'])){
										if(isset($element['handle~']['emailAddress'])){
											$email = $element['handle~']['emailAddress'];
											break;
										}
									}
									
								}
								
							}
						}
						
						if($this->app['helper']('Utility')->notEmpty($email)){
							
							$getProfileInfo = \Requests::get(self::linkedin_profile_url, $headers);
							$profileInfo = $getProfileInfo->body;

							$userInfo = $this->app['helper']('Utility')->decodeJson($profileInfo);

							$userDetails = self::userInfo($userInfo, $email);
							$SocialHp = new SocialHp($this->app);
							return $SocialHp->signInSocialUser($userDetails,'linkedin');
							
						}else{
							// error to get user email
							$this->app['monolog.debug']->error("can not get email from linked signIn");
							$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));

							return ['status'=>'error','message'=>$msg];
						}

					}else{
						// error occurred in get access token
						$this->app['monolog.debug']->error("can not get access token from linked signIn");
						$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));

							return ['status'=>'error','message'=>$msg];
					}
					
				}
				
			}

		}
			

	}

	private function userInfo($userInfo,$email){
		
		$details = [];
		//$details['first_name'] = $userInfo['firstName'];
		$details['first_name'] = $userInfo['localizedFirstName'];
		//$details['last_name'] = $userInfo['lastName'];
		$details['last_name'] = $userInfo['localizedLastName'];
		//$details['email'] = $userInfo['emailAddress'];
		$details['email'] = $email;
		//$details['username'] = $userInfo['emailAddress'];
		//$details['address1'] = $userInfo['location']['name'];
		$details['address1'] = '';
		$details['signup_source'] = 'Linkedin';
		$details['password'] = $this->app['helper']('CryptoGraphy')->randomPassword(10);
		
		return $details;
		
	}
	
	public function linkedin_redirect_uri(){
		
		return $this->app['baseUrl'].'socialoauth/linkedin';
		
	}
	
}
