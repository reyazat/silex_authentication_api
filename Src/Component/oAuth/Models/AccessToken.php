<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as Capsule;
class AccessToken extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'access_token';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
		
    }
	public function saveAccessToken($details = []){
		
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($details)){
			if(isset($details['access_token_id']) && $this->app['helper']('Utility')->notEmpty($details['access_token_id'])){
				
				$save = AccessToken::insert($details);
				return $save;
				
			}
		}
		
		return false;
		
	}
	
	public function removeAccessToken($accessTokenId = ''){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($accessTokenId)){
			
			$accessTokenId = $this->app['helper']('Utility')->trm($accessTokenId);
			$remove = AccessToken::where('access_token_id','=',$accessTokenId)->delete();
			return $remove;
			
		}else{
			return false;
		}
		
	}
	
	public function removeAccessTokenOptional($userIdentify = '',$clientId = ''){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($userIdentify) && $this->app['helper']('Utility')->notEmpty($clientId)){

			$remove = AccessToken::where('user_identify','=',$userIdentify)
								->where('id_client','=',$clientId)
								->delete();
			return $remove;
			
		}else{
			return false;
		}
		
	}
	
	public function accessTokenExist($accessTokenId = ''){

		if($this->app['helper']('Utility')->notEmpty($accessTokenId)){

			$find = AccessToken::where('access_token_id','=',$accessTokenId)
								->get();
			
			$this->app['helper']('ModelLog')->Log();
			if(isset($find[0]) && $this->app['helper']('Utility')->notEmpty($find[0])){
				
				return false;
				
			}else{
				
				return true;
				
			}
			
		}else{
			
			return true;
			
		}
		
	}
	
}