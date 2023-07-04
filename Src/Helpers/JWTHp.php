<?php
namespace Helper;
use \Firebase\JWT\JWT;
class JWTHp{
	
	protected $app;
	
	public function __construct($app) {
       	
		$this->app = $app;
	}
	
	public function createToken($params){
		$jwtData = [];
		if(isset($params['user_id']) && $this->app['helper']('Utility')->notEmpty($params['user_id']))
			$jwtData['id_user'] = $params['user_id'];
		
		if(isset($params['username']) && $this->app['helper']('Utility')->notEmpty($params['username']))
			$jwtData['username'] = $params['username'];
		
		if(isset($params['user_type']) && $this->app['helper']('Utility')->notEmpty($params['user_type']))
			$jwtData['user_type'] = $params['user_type'];
		
		if(isset($params['parent_id']) && $this->app['helper']('Utility')->notEmpty($params['parent_id']))
			$jwtData['parent_id'] = $params['parent_id'];
		
		if(isset($params['site_id']) && $this->app['helper']('Utility')->notEmpty($params['site_id']))
			$jwtData['site_id'] = $params['site_id'];
		
		if(isset($params['unique_token']) && $this->app['helper']('Utility')->notEmpty($params['unique_token']))
			$jwtData['token'] = $params['unique_token'];
		
		if(isset($params['source']) && $this->app['helper']('Utility')->notEmpty($params['source']))
			$jwtData['source'] = $params['source'];
		
		if(isset($params['cDate']) && $this->app['helper']('Utility')->notEmpty($params['cDate']))
			$jwtData['cDate'] = $params['cDate'];
		
		$privateKey = $this->app['helper']('Utility')->readFile($this->app['baseDir'] . '/Keys/jwtRS256.key');
        
		return JWT::encode($jwtData, $privateKey, 'RS256');
	}
	
	public function verifyToken($authCode = ''){
		if($this->app['helper']('Utility')->notEmpty($authCode)){
			$publicKey = $this->app['helper']('Utility')->readFile($this->app['baseDir'].'/Keys/jwtRS256.key.pub');
			try {
				$decoded = JWT::decode($authCode, $publicKey, array('RS256'));
				$data = (array)$decoded;
			} catch (\Exception $e) {
				return (['status'=>'Error','message'=>$e->getMessage(),'code'=>403]);
			}
		}else{
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Token'));
			$payLoad = ['status'=>'Error','message'=>$msg,'code'=>400];
			return ($payLoad);
		}
		return ['status'=>'Success','message'=>'','code'=>200,'data'=>$data];
	}
	
}