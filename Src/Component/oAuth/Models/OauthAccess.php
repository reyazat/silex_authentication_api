<?php
namespace Component\oAuth\Models;



class OauthAccess extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'oauth_access';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function checkDuplicateOauthAccess($details = []){
		$this->app['helper']('ModelLog')->Log();
		$checkDuplicate = OauthAccess::select('id')
									->where('user_id','=',$details['user_id'])
									->where('client_id','=',$details['client_id'])
									->take(1)
									->get();
		if(isset($checkDuplicate[0]) && $this->app['helper']('Utility')->notEmpty($checkDuplicate[0])){
			
			return $checkDuplicate[0]->id;
			
		}else{
			
			return '';
			
		}
	}
	
	public function checkRefreshTokenRevoked($RefreshTokenId,$userId){
		$this->app['helper']('ModelLog')->Log();
		$checkDuplicate = OauthAccess::select('id')
									->where('refresh_token_id','=',$RefreshTokenId)
									->where('user_id','=',$userId)
									->get();
		
		if(isset($checkDuplicate[0]) && $this->app['helper']('Utility')->notEmpty($checkDuplicate[0])){
			
			return $checkDuplicate[0]->id;
			
		}else{
			
			return '';
			
		}
		
	}
	
	public function checkAccessTokenRevoked($AccessTokenId,$userId){
		$this->app['helper']('ModelLog')->Log();
		$checkDuplicate = OauthAccess::select('id')
									->where('access_token_id','=',$AccessTokenId)
									->where('user_id','=',$userId)
									->get();
		
		if(isset($checkDuplicate[0]) && $this->app['helper']('Utility')->notEmpty($checkDuplicate[0])){
			
			return $checkDuplicate[0]->id;
			
		}else{
			
			return '';
			
		}
		
	}
	
	public function insertOauthAccess($details = []){
		$this->app['helper']('ModelLog')->Log();
		$details = array_filter($details);
		if($this->app['helper']('Utility')->notEmpty($details)){
			
			$idOauth = OauthAccess::insertGetId($details);
			
			if($this->app['helper']('Utility')->notEmpty($idOauth)){
				
				return $idOauth;
				
			}else{
				
				return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}
			
		}else{
			
			return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
			
		}

		
	}
	
	public function updateOauthAccess($details = [],$id){
		$this->app['helper']('ModelLog')->Log();
		$details = array_filter($details);
		if( $this->app['helper']('Utility')->notEmpty($details) && $this->app['helper']('Utility')->notEmpty($id) ){
			
			$update = OauthAccess::where('id',$id)->update($details);
			
			if( $this->app['helper']('Utility')->notEmpty($update)){
				
				return $id;
				
			}else{
				
				return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}
			
		}else{
			
			return ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
			
		}
		
	}
	
	
}