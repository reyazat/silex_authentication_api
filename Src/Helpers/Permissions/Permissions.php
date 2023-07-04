<?php
namespace Helper\Permissions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Firebase\JWT\JWT;

class Permissions{
	
	protected $app;
	
	public function __construct($app) {
		
		$this->app = $app;
		
	}
	
	public function init($authCode = '', $credential = ''){
		$access = false;
		$route = $this->app['request_content']->getPathInfo();
		$route = $this->app['helper']('Utility')->trm($route);

		foreach($this->app['config']['anonymousUrlContain'] as $row){
			if (!preg_match($row, $route)){
				continue;
			}else{
				$access = true;
			}
		}
		
		if($access === false){
			
			$anonymousRoute = $this->app['config']['anonymousRoute'];

			if (!in_array($route, $anonymousRoute)){
				$access = $this->check_access($authCode, $credential);
			}else{
				$access = true;
			}
			
		}
		return $access;
		
	}
	
	public function check_access($authCode = '', $credential = ''){

		if($this->app['helper']('Utility')->notEmpty($authCode) && 
		   $this->app['helper']('Utility')->notEmpty($credential) ){
			
			$checkAccess = $this->app['helper']('JWTHp')->verifyToken($authCode);
			if ($checkAccess['status'] === 'Success') {
				$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
                if ($getLoginSource['status'] === 'Success') {
					$this->app['oauth'] = $checkAccess['data'];
					$cacheId = $this->app['helper']('CryptoGraphy')->urlsafe_b64encode($checkAccess['data']['id_user'].'-'.$credential);

					$checkJwt = $this->app['cache']->fetch($cacheId);
					
					if($checkJwt != $authCode){
						
						$msg = $this->app['translator']->trans('AccessDenied', array());
						return setResponse(['status'=>'Error','message'=>$msg,'code'=>401]);
						
						
					}
				}else{
					return setResponse($getLoginSource);
				}				
			} else {
                 return setResponse($checkAccess);
            }
			
		}else{
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Token, Credential'));
			return setResponse(['status'=>'Error','message'=>$msg,'code'=>400]);

			
		}

		return true;
		
	}
	
}