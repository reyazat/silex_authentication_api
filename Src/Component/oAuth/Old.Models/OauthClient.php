<?php
namespace Component\oAuth\Models;

class OauthClient extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'oauth_client';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function findClient($clientId,$clientSecret){
		$this->app['helper']('ModelLog')->Log();
		$Client = OauthClient::where('client_id','=',$clientId)->where('client_secret','=',$clientSecret)->get();
		
		if(isset($Client[0]) && $this->app['helper']('Utility')->notEmpty($Client[0])){
			
			return $Client[0]->toArray();
			
		}else{
			return [];
		}
		
	}
	
	public function getClientByName($clientName){
		$this->app['helper']('ModelLog')->Log();
		$Client = OauthClient::select('client_id','client_secret')->where('app_name','=',$clientName)->get();
		if(isset($Client[0]) && $this->app['helper']('Utility')->notEmpty($Client[0])){
			
			return $Client[0]->toArray();
			
		}else{
			
			return ['status'=>'error','message'=>'Client with selected details not exist.'];
			
		}
		
	}
	
	public function smartyClient(){
		$this->app['helper']('ModelLog')->Log();
		$findClient = OauthClient::getClientByName('smarty');
		
		$res = [];
		$res['client_id'] = $findClient['client_id'];
		$res['client_secret'] = $findClient['client_secret'];
		
		return $res;
		
	}
	
}