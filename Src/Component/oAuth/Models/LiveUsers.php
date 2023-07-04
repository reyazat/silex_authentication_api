<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;

class LiveUsers extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'live_user';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeFields($params = []){
		
		$fields = [];
		if(array_key_exists('id_user',$params)){
			$fields['id_user'] = $params['id_user'];
		}
		if(array_key_exists('id_company',$params)){
			$fields['id_company'] = $params['id_company'];
		}
		if(array_key_exists('url',$params)){
			$fields['url'] = $params['url'];
		}
		
		return $fields;
		
	}
	
	public function insertUser($params = []){
		
		$payLoad = [];
	 	if(!isset($params['id_user']) || 
			(isset($params['id_user']) && !$this->app['helper']('Utility')->notEmpty($params['id_user'])) || 
			!isset($params['id_company']) || 
			(isset($params['id_company']) && !$this->app['helper']('Utility')->notEmpty($params['id_company'])) ||
			!isset($params['url']) || 
			(isset($params['url']) && !$this->app['helper']('Utility')->notEmpty($params['url']))){

				$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Owner Company,Id User, Url'));
				$payLoad = ['status'=>'error','message'=>$msg];

			}else{
				$fields = self::makeFields($params);
				$fields['create_date'] = date('Y-m-d');
				$fields['created_at'] = date('Y-m-d H:i:s');
				LiveUsers::insertGetId($fields);
				$payLoad = ['status'=>'success'];
			}
		
		return $payLoad;
		
	}
	
}