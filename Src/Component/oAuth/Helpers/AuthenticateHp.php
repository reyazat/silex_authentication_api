<?php

namespace Component\oAuth\Helpers;
use League\OAuth2\Server\AuthorizationServer;
use OAuth2ServerExamples\Repositories\ClientRepository;
use OAuth2ServerExamples\Repositories\AccessTokenRepository;
use OAuth2ServerExamples\Repositories\ScopeRepository;
use OAuth2ServerExamples\Repositories\UserRepository;
use OAuth2ServerExamples\Repositories\RefreshTokenRepository;

use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\CryptKey;

use Component\oAuth\Assets\ConstantOauth;

class AuthenticateHp extends ConstantOauth{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	private function AuthorizationServer(){
		
		// Init our repositories
		$clientRepository = new ClientRepository($this->app); // instance of ClientRepositoryInterface
		$accessTokenRepository = new AccessTokenRepository($this->app); // instance of AccessTokenRepositoryInterface
		$scopeRepository = new ScopeRepository($this->app); // instance of ScopeRepositoryInterface
		
		// Setup the authorization server
		$server = new AuthorizationServer(
			$clientRepository,
			$accessTokenRepository,
			$scopeRepository,
			self::privateKey,
			self::encryptionKey
		);
		
		return $server;
		
	}
	
	private function responseToAccessTokenRequest($server,$symfonyToPsr){
		
		$payLoad = [];
		try {
			// Try to respond to the request
			$res = $server->respondToAccessTokenRequest($symfonyToPsr['request'], $symfonyToPsr['response']);
			$psrToSymfoni = $this->app['helper']('SymfonyPsrBridge')->psr2symfony(NULL,$res);
		
        	$payLoad = $this->app['helper']('Utility')->convertResponseToArray($psrToSymfoni['response']);

		} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
			
			// All instances of OAuthServerException can be formatted into a HTTP response
			//$payLoad = $exception->generateHttpResponse($symfonyToPsr['response']);
			return self::errorResponse($exception);

		} catch (\Exception $exception) {
			
			// Unknown exception
			return ['status'=>'error',
					'status code'=>500,
					'code'=>500,
					'message'=>$exception->getMessage()];

		}
		
		return $payLoad;
		
	}
	
	private function errorResponse($exception){
		
		$statusCode = $exception->gethttpStatusCode();
		$errorType = $exception->geterrorType();
		$message = $exception->getmessage();
		$hint = $exception->gethint();

		return ['status'=>'error',
				'status code'=>$statusCode,
				'code'=>$statusCode,
				'message'=>$message,
				'hint'=>$hint,
				'error type'=>$errorType];
		
	}
	
	public function getAccessToken($request){

		$server = self::AuthorizationServer();
		
		$userRepository = new UserRepository($this->app); // instance of UserRepositoryInterface
		$refreshTokenRepository = new RefreshTokenRepository($this->app); // instance of RefreshTokenRepositoryInterface
		
		$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
			 $userRepository,
			 $refreshTokenRepository
		);
		
		$grant->setRefreshTokenTTL(new \DateInterval(self::refreshTokenExpire)); // refresh tokens will expire after 1 month

		// Enable the password grant on the server
		$server->enableGrantType(
			$grant,
			new \DateInterval(self::accessTokenExpire) // access tokens will expire after 1 hour
		);

		$symfonyToPsr = $this->app['helper']('SymfonyPsrBridge')->symfony2psr($request);
		
		return self::responseToAccessTokenRequest($server,$symfonyToPsr);
		
	}
	
	public function updateAccessToken($request){
		
		// Init our repositories
		$refreshTokenRepository = new RefreshTokenRepository($this->app);

		// Setup the authorization server
		$server = self::AuthorizationServer();
		
		$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant($refreshTokenRepository);
		$grant->setRefreshTokenTTL(new \DateInterval(self::refreshTokenExpire)); // new refresh tokens will expire after 1 month

		// Enable the refresh token grant on the server
		$server->enableGrantType(
			$grant,
			new \DateInterval(self::accessTokenExpire) // new access tokens will expire after an hour
		);
		
		$symfonyToPsr = $this->app['helper']('SymfonyPsrBridge')->symfony2psr($request);

		return self::responseToAccessTokenRequest($server,$symfonyToPsr);
		
		
	}
	
	public function validateAccessToken($request){
		
		
		$accessToken = $this->app['helper']('Utility')->notEmpty($request->get('access_token'))?$request->get('access_token'):NULL;
		if(isset($accessToken) &&  $this->app['helper']('Utility')->notEmpty($accessToken)){
			
			// set access token as header
			$request->server->set('HTTP_AUTHORIZATION', 'Bearer '.$request->get('access_token'));
			$request->headers->set('authorization', ['Bearer '.$request->get('access_token')]);
			
		}
		
		$symfonyToPsr = $this->app['helper']('SymfonyPsrBridge')->symfony2psr($request);
		
		$accessTokenRepository = new AccessTokenRepository($this->app); // instance of AccessTokenRepositoryInterface
		
		$keyPath = new CryptKey(self::publicKey);
		
		$validator = new BearerTokenValidator($accessTokenRepository);
		
		$validator->setPublicKey($keyPath);
		
		
		try {
			// Try to respond to the request
			$result = $validator->validateAuthorization($symfonyToPsr['request']);
			

		} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
			
			// All instances of OAuthServerException can be formatted into a HTTP response
			return self::errorResponse($exception);
			
		}
		
		
		$psrToSymfoni = $this->app['helper']('SymfonyPsrBridge')->psr2symfony($result);

		$payLoad = [];
		$payLoad['access_token_id'] = $psrToSymfoni['request']->get('oauth_access_token_id');
		$payLoad['client_id'] = $psrToSymfoni['request']->get('oauth_client_id');
		$payLoad['user_id'] = $psrToSymfoni['request']->get('oauth_user_id');
		//$payLoad['oauth_scopes'] = $psrToSymfoni['request']->get('oauth_scopes');
		$payLoad['expire'] = $psrToSymfoni['request']->get('expire');

        return $payLoad;
		
	}
	
	public function recaptcha($response){
		
		$payLoad = [];
		
		if(!$this->app['helper']('Utility')->notEmpty($response)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty.'];
			
		}else{
				
			$res = $this->app['helper']('OutgoingRequest')->postRequest(self::googleCaptchaUrl,[],['secret' => self::googleCaptchaSecret,'response' => $response]);
			
			if($res['success'] === true){
				
				$payLoad = ['status'=>'success','message'=>'Captcha validated.'];
				
			}else{
				
				$payLoad = ['status'=>'error','message'=>'Captcha not validated.'];
				
			}
			
			return $payLoad;
			
		}


	}
	
	public function appReload($parameters){
		
		$payLoad = [];
		if(!isset($parameters['username']) || 
		   !isset($parameters['password']) || 
		   !isset($parameters['client_id']) || 
		   !isset($parameters['client_secret']) || 
		  /* !isset($parameters['id_pipeline']) ||*/ 
		   !isset($parameters['id_company'])){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name, Password, Client id, Client secret, Id Company'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			// request for access token
			$getAccessToken = $this->app['helper']('HandlleRequest')->returnResultData('/authenticate/accesstoken',
																 'POST',
																 ['username'=>$parameters['username'],
																  'password'=>$parameters['password'],
																  'client_id'=>$parameters['client_id'],
																  'client_secret'=>$parameters['client_secret']]);
			
			
			if(isset($getAccessToken['access_token'])){
				
				$payLoad['access_token'] = $getAccessToken['access_token'];
				// get id_user from access token
				$validateAccess = $this->app['helper']('HandlleRequest')->returnResultData('/authenticate/validate','POST',['access_token'=>$getAccessToken['access_token']]);
				
				if(isset($validateAccess['user_id'])){
					
					if(isset($parameters['ip'])){
						$getDeviceType = $this->app['load']('Component_oAuth_Models_OauthClient')->findClient($parameters['client_id'], $parameters['client_secret']);
						if(isset($getDeviceType['app_name'])){
							$this->app['load']('Component_oAuth_Models_LoginIp')->addLogin(['user_id'=>$validateAccess['user_id'],'ip'=>$parameters['ip'],'device'=>$getDeviceType['app_name']]);
						}
					}
					
					if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
						$ip = $_SERVER['HTTP_CLIENT_IP'];
					} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
						$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					} else {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
					
					// save login ip
					$this->app['helper']('OutgoingRequest')->postRequest(
						$this->app['config']['webservice']['view'].'system/ip',
						[],
						['ip'=>$ip,
						 'id_user'=>$validateAccess['user_id']]);
					
					$idUser = $validateAccess['user_id'];
					$payLoad['pic'] = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['view'].'setting/userpic/'.$idUser,[],[]);
					
					$idCompany = $parameters['id_company'];
					// get company details
					$companyDetails = $this->app['helper']('HandlleRequest')->returnResultData('/user/info','POST',['access_token'=>$getAccessToken['access_token'],'action'=>'identify&company','id_company'=>$idCompany,'id_user'=>$idUser]);
					
					if(isset($companyDetails['id'])){
						if(!$this->app['helper']('Utility')->notEmpty($companyDetails['timezone'])){
							$companyDetails['timezone'] = 'Europe/London';
						}
						
						if(!$this->app['helper']('Utility')->notEmpty($companyDetails['locale'])){
							$companyDetails['locale'] = 'en_GB';
						}
						if(!$this->app['helper']('Utility')->notEmpty($companyDetails['currency'])){
							$companyDetails['currency'] = 'GBP';
						}
						
						$payLoad['account_details'] = $companyDetails;

						
						$getPipelineList = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'pipeLine','owner_company'=>$idCompany]);

						if(count($getPipelineList) > 0 and isset($getPipelineList[0]['id'])){

							$payLoad['pipelines'] = $getPipelineList;
								
							if(isset($parameters['id_pipeline'])){
								$idPipeline = $parameters['id_pipeline'];
							}else{
								$findDefault = array_search ('default', array_column($getPipelineList, 'type'));
								if($findDefault !== false){
									$idPipeline = $getPipelineList[$findDefault]['id']; 
								}else{
									$idPipeline = $getPipelineList[0]['id']; 
								}
							}
							
							$getStages = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'stage','id_pipeline'=>$idPipeline,'owner_company'=>$idCompany]);

							$payLoad['stages'] = $getStages;

							$getActivityType = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'activityType','owner_company'=>$idCompany]);

							$payLoad['activity_type'] = $getActivityType;

							$getChild = $this->app['helper']('HandlleRequest')->returnResultData('/user/company','POST',['access_token'=>$getAccessToken['access_token'],'id_user'=>$idUser,'id_company'=>$idCompany,'action'=>'child']);

							if(isset($getChild['status']) && $getChild['status'] == 'error'){

								$payLoad['children'] = ['identify'=>$idUser,'name'=>'Myself'];

							}else{
								$payLoad['children'] = $getChild;
							}

							$allUser = $this->app['helper']('HandlleRequest')->returnResultData('/user/list','POST',['access_token'=>$getAccessToken['access_token'],'id_company'=>$idCompany,'action'=>'all']);

							$payLoad['all_user'] = $allUser;

							$currencyList = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['setting'].'utility/currency',[],[]);
										
							$payLoad['curencies'] = $currencyList;

							$findCurrency = array_search ($payLoad['account_details']['currency'], array_column($currencyList, 'key'));
							if($findCurrency != false){
								$payLoad['curency_details'] = $currencyList[$findCurrency];
							}

							$payLoad['locale'] = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['setting'].'utility/locale',[],['action'=>'details','locale'=>$payLoad['account_details']['locale']]);


						}else{
							$payLoad = $getPipelineList;
						}

					}else{
						$payLoad = $companyDetails;
					}
					
				}else{// error in get access token
					$payLoad = $getAccessToken['access_token'];
				}
				
			}else{// error in get access token
				$payLoad = $getAccessToken;
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function appFirstLogin($parameters){ 
		
		$payLoad = [];
		if(!isset($parameters['username']) || 
		   !isset($parameters['password']) || 
		   !isset($parameters['client_id']) || 
		   !isset($parameters['client_secret'])){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name, Password, Client id, Client secret'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			// request for access token
			$getAccessToken = $this->app['helper']('HandlleRequest')->returnResultData('/authenticate/accesstoken',
																 'POST',
																 ['username'=>$parameters['username'],
																  'password'=>$parameters['password'],
																  'client_id'=>$parameters['client_id'],
																  'client_secret'=>$parameters['client_secret']]);
			
			if(isset($getAccessToken['access_token'])){
				
				$payLoad['access_token'] = $getAccessToken['access_token'];
				$payLoad['refresh_token'] = $getAccessToken['refresh_token'];
				// get id_user from access token
				$validateAccess = $this->app['helper']('HandlleRequest')->returnResultData('/authenticate/validate','POST',['access_token'=>$getAccessToken['access_token']]);
				
				if(isset($validateAccess['user_id'])){
					
					if(isset($parameters['ip'])){
						$getDeviceType = $this->app['load']('Component_oAuth_Models_OauthClient')->findClient($parameters['client_id'], $parameters['client_secret']);
						if(isset($getDeviceType['app_name'])){
							$this->app['load']('Component_oAuth_Models_LoginIp')->addLogin(['user_id'=>$validateAccess['user_id'],'ip'=>$parameters['ip'],'device'=>$getDeviceType['app_name']]);
						}
					}
					
					if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
						$ip = $_SERVER['HTTP_CLIENT_IP'];
					} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
						$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					} else {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
					
					// save login ip
					$this->app['helper']('OutgoingRequest')->postRequest(
						$this->app['config']['webservice']['view'].'system/ip',
						[],
						['ip'=>$ip,
						 'id_user'=>$validateAccess['user_id']]);
					// save device token(firebase)
					if(isset($parameters['device_token']) && $parameters['device_type']){
						$this->app['component']('oAuth_Models_DeviceToken')->addToken($validateAccess['user_id'], $parameters['device_token'], $parameters['device_type']);
					}
					
					$idUser = $validateAccess['user_id'];
					// get user info with action: identify
					$userDetails = $this->app['helper']('HandlleRequest')->returnResultData('/user/info','POST',['access_token'=>$getAccessToken['access_token'],'action'=>'identify','id_user'=>$idUser]);
					
					if(isset($userDetails['id'])){
						
						$payLoad['pic'] = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['view'].'setting/userpic/'.$idUser,[],[]);
						
						if(!$this->app['helper']('Utility')->notEmpty($userDetails['locale'])){
							$userDetails['locale'] = 'en_GB';
						}
						if(!$this->app['helper']('Utility')->notEmpty($userDetails['currency'])){
							$userDetails['currency'] = 'GBP';
						}
						$payLoad['account_details'] = $userDetails;

						// get list of user company
						$companyList = $this->app['helper']('HandlleRequest')->returnResultData('/user/company','POST',['access_token'=>$getAccessToken['access_token'],'action'=>'list','id_user'=>$idUser]);
						
						$payLoad['company_count'] = count($companyList);
						if(count($companyList) > 0){
							
							if(count($companyList) == 1){
								
								$idCompany = $companyList[0]['id'];
								// get company details
								$companyDetails = $this->app['helper']('HandlleRequest')->returnResultData('/user/info','POST',['access_token'=>$getAccessToken['access_token'],'action'=>'identify&company','id_company'=>$idCompany,'id_user'=>$idUser]);
	
								if(isset($companyDetails['id'])){
									if(!$this->app['helper']('Utility')->notEmpty($companyDetails['timezone'])){
										$companyDetails['timezone'] = 'Europe/London';
									}
									if(!$this->app['helper']('Utility')->notEmpty($companyDetails['locale'])){
										$companyDetails['locale'] = 'en_GB';
									}
									if(!$this->app['helper']('Utility')->notEmpty($companyDetails['currency'])){
										$companyDetails['currency'] = 'GBP';
									}
									$payLoad['account_details'] = $companyDetails;
									
									$getPipelineList = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'pipeLine','owner_company'=>$idCompany]);
									
									if(count($getPipelineList) > 0 and isset($getPipelineList[0]['id'])){
										
										$payLoad['pipelines'] = $getPipelineList;
										$findDefault = array_search ('default', array_column($getPipelineList, 'type'));
										if($findDefault !== false){
											$idPipeline = $getPipelineList[$findDefault]['id']; 
										}else{
											$idPipeline = $getPipelineList[0]['id']; 
										}
										
										$getStages = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'stage','id_pipeline'=>$idPipeline,'owner_company'=>$idCompany]);
										
										$payLoad['stages'] = $getStages;
										
										$getActivityType = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['crm'].'setting/',[],['access_token'=>$getAccessToken['access_token'],'action'=>'activityType','owner_company'=>$idCompany]);
										
										$payLoad['activity_type'] = $getActivityType;
										
										$getChild = $this->app['helper']('HandlleRequest')->returnResultData('/user/company','POST',['access_token'=>$getAccessToken['access_token'],'id_user'=>$idUser,'id_company'=>$idCompany,'action'=>'child']);

										if(isset($getChild['status']) && $getChild['status'] == 'error'){

											$payLoad['children'] = ['identify'=>$idUser,'name'=>'Myself'];

										}else{
											$payLoad['children'] = $getChild;
										}
										
										$allUser = $this->app['helper']('HandlleRequest')->returnResultData('/user/list','POST',['access_token'=>$getAccessToken['access_token'],'id_company'=>$idCompany,'action'=>'all']);
										
										$payLoad['all_user'] = $allUser;

										$currencyList = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['setting'].'utility/currency',[],[]);
										
										$payLoad['curencies'] = $currencyList;
										
										$findCurrency = array_search ($payLoad['account_details']['currency'], array_column($currencyList, 'key'));
										if($findCurrency != false){
											$payLoad['curency_details'] = $currencyList[$findCurrency];
										}
										
										$payLoad['locale'] = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['setting'].'utility/locale',[],['action'=>'details','locale'=>$payLoad['account_details']['locale']]);
										
										
									}else{
										$payLoad = $getPipelineList;
									}
									
								}else{
									$payLoad = $companyDetails;
								}
								
							}else{
								$payLoad['company_list'] = $companyList;
							}
							
						}else{// have not any company
							return $payLoad;
						}
						
						
					}else{// error in get user info with action:identify
						$payLoad = $userDetails;
					}
					
					
				}else{// error in validate access token and get id_user
					$payLoad = $validateAccess;
				}
				
			}else{// error in get access token
				$payLoad = $getAccessToken;
			}
			
		}
		
		return $payLoad;
		
	}
}
