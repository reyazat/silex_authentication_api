<?php
namespace Component\oAuth\Models;

class IpInfo extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'ip_info';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeField($params = []){
		
		$fields = [];
		if(array_key_exists('ip',$params)){
			$fields['ip'] = $params['ip'];
		}
		
		if(array_key_exists('iso_code',$params)){
			$fields['iso_code'] = $params['iso_code']; 
		}
		
		return $fields;
		
	}
	
	public function checkIp($ip = ''){
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($ip)){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Ip'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			$payLoad = ['status'=>'success','data'=>[]];
			$findIp = IpInfo::where('ip','=',$ip)->get();
			if(isset($findIp[0])){
				$payLoad['data'] = $findIp[0]->toArray();
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function addIp($params = []){
		
		$payLoad = [];
		$fields = $this->makeField($params);
		if(!isset($fields['ip']) || 
		   (isset($fields['ip']) && !$this->app['helper']('Utility')->notEmpty($fields['ip'])) || 
		   !isset($fields['iso_code']) || 
		   (isset($fields['iso_code']) && !$this->app['helper']('Utility')->notEmpty($fields['iso_code']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Ip, Country Code'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			IpInfo::insertGetId($fields);
			$msg = $this->app['translator']->trans('add', array('%name%' => 'Ip'));
			$payLoad = ['status'=>'success','message'=>$msg];
		}
		
	}
	
}