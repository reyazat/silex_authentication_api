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
	
}
