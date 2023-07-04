<?php
namespace Component\oAuth\Models;

class DeviceToken extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'device_token';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
		
	public function addToken($idUser = '', $token = '', $deviceType = ''){
		
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($idUser) && 
		   $this->app['helper']('Utility')->notEmpty($token) && 
		   $this->app['helper']('Utility')->notEmpty($deviceType)){
			
			$checkDuplicate = DeviceToken::select('id')->where('token','=',$token)->where('type','=',$deviceType)->where('id_user','=',$idUser)->get();
			
			if(!isset($checkDuplicate[0])){ // token not exist
				
				DeviceToken::insertGetId(['token'=>$token,'type'=>$deviceType,'id_user'=>$idUser]);

			}
			
			$msg = $this->app['translator']->trans('add', array('%name%' => 'Token'));
			$payLoad = ['status'=>'success','message'=>$msg];
			
		}else{
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User, Device token, Device type'));
			$payLoad = ['status'=>'error','message'=>$msg];
		}
		
		return $payLoad;
		
	}
	
	public function getDevice($idUser = ''){
		
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($idUser)){
			
			$devices = DeviceToken::select('token','type')->where('id_user','=',$idUser)->get()->toArray();
			$payLoad = ['status'=>'success','date'=>$devices];
			
		}else{
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
			$payLoad = ['status'=>'error','message'=>$msg];
		}
		
		return $payLoad;
		
	}
	
}