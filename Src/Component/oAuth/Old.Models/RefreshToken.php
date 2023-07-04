<?php
namespace Component\oAuth\Models;




class RefreshToken extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'refresh_token';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
		
    }
	public function saveRefreshToken($details = []){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($details['access_token_id']) && $this->app['helper']('Utility')->notEmpty($details['refresh_token_id'])){
			
			if($this->app['helper']('Utility')->notEmpty($details['user_identify']) && $this->app['helper']('Utility')->notEmpty($details['id_client'])){
				
				$save = RefreshToken::insert($details);
				return $save;
				
			}
			
		}
		
		return false;
		
	}
	
	public function removeRefreshToken($tokenId = ''){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($tokenId)){
			
			$remove = RefreshToken::where('refresh_token_id','=',$tokenId)->delete();
			return $remove;
			
		}else{
			
			return false;
			
		}
		
	}
	
	
	public function removeRefreshTokenOptional($userIdentify,$clientId){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($userIdentify) && $this->app['helper']('Utility')->notEmpty($clientId)){
			
			$remove = RefreshToken::where('user_identify','=',$userIdentify)
									->where('id_client','=',$clientId)
									->delete();
			return $remove;
			
		}else{
			
			return false;
			
		}
		
	}
	
	
	public function refreshTokenExist($refreshTokenId = ''){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($refreshTokenId)){
			
			$find = RefreshToken::where('refresh_token_id','=',$refreshTokenId)
								->get();
			
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