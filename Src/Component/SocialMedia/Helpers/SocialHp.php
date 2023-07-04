<?php

namespace Component\SocialMedia\Helpers;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Component\oAuth\Models\SoftwareUser;
use Component\oAuth\Models\OauthClient;

use Component\SocialMedia\Helpers\FacebookHp;
use Component\SocialMedia\Helpers\LinkedinHp;
use Component\SocialMedia\Helpers\GoogleHp;
use Helper\CryptoGraphy;
class SocialHp{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function signInSocialUser($userDetails,$source){
		$SoftwareUser = new SoftwareUser($this->app);
		$checkDuplicate = $SoftwareUser->checkDuplicateUser($userDetails['email']);
		if($checkDuplicate === false){
	
			$signUpUser = $this->app['helper']('HandlleRequest')->returnResult('/authenticate/signup','POST',$userDetails);
			$signUpUserArr = $this->app['helper']('Utility')->convertResponseToArray($signUpUser);
			
			if(isset($signUpUserArr['status']) && $signUpUserArr['status'] == 'error'){
				
				// error in add new user in signup
				return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}else{
				
				//signup successfully
				$idUser = $signUpUserArr['id_user'];
				
			}
			
		}else{
			
			$idUser = $checkDuplicate;
			
		}
		
		$findUser = $SoftwareUser->getUserByIdentify($idUser);
		if(isset($findUser['status']) && $findUser['status'] == 'error'){
			
			// error, user must be exist
			return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
			
		}else{
			
			$this->app['predis']['cache']->del('accessAccIntegrate_'.$findUser['identify']);
			$this->app['predis']['cache']->set('accessAccIntegrate_'.$findUser['identify'],serialize([]));
			$this->app['predis']['cache']->expire('accessAccIntegrate_'.$findUser['identify'], 60);// will be 60 second
			
			$CryptoGraphy = new CryptoGraphy($this->app);
			$postData = [];
			$postData['username'] = $findUser['email'];
			//$postData['password'] = $CryptoGraphy->md5decrypt($findUser['password']);
			$postData['password'] = 'accIntegrate#2020@';
			$postData['webLogin'] = 1;
			
			/*$findClient = OauthClient->getClientByName('smarty');
			
			$postData['client_id'] = $findClient['client_id'];
			$postData['client_secret'] = $findClient['client_secret'];*/
			
			$signInUser = $this->app['helper']('HandlleRequest')->returnResult('/authenticate/signin','POST',$postData);
			$res = $this->app['helper']('Utility')->convertResponseToArray($signInUser);
			
			return $res;
			
		}
		
	}
	
	public function loginResult($res){
		
		if(isset($res['status']) && $res['status'] == 'error'){
			// error occurred in login user
			return new RedirectResponse($this->app['config']['webservice']['view'].'system/signin?error='.$res['message']);
		}else{
			
			/*['access_token'=>$res['access_token'],
										   'refresh_token'=>$res['refresh_token']]);*/

			/*setcookie('access_token', $res['access_token'], time() + 60, 'http://view.smartysoftware.net');
			setcookie('refresh_token', $res['refresh_token'], time() + 60, 'http://view.smartysoftware.net');
			header('location: '.$this->app['config']['webservice']['view'].'system/signin?action=sociallogin');exit;*/

				
			/*$flashBag = $this->app['session']->getFlashBag();
			$flashBag->set('authenticate', ['access_token'=>$res['access_token'],
										   'refresh_token'=>$res['refresh_token']]);

			return $this->app->redirect($this->app['config']['webservice']['view'].'system/signin?action=sociallogin');*/
		
			return new RedirectResponse($this->app['config']['webservice']['view'].'system/signin?action=sociallogin&access_token='.$res['access_token'].'&refresh_token='.$res['refresh_token']);
			
		}
		
	}
	
	public function getSocialLoginLink(){
		$LinkedinHp = new LinkedinHp($this->app);
		$FacebookHp = new FacebookHp($this->app);
		$GoogleHp = new GoogleHp($this->app);
		
		$linkedInSignInLink = $LinkedinHp->linkedinLoginLink();
		$facebookSignInLink = $FacebookHp->facebookLoginLink();
		$googleSignInLink = $GoogleHp->googleLoginLink();
		
		return ['facebook'=>$facebookSignInLink,'linkedin'=>$linkedInSignInLink,'google'=>$googleSignInLink];
		
	}

	
}
