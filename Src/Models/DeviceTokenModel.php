<?php

namespace Models;

use Illuminate\Pagination\Paginator;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Query\Expression as raw;

class DeviceTokenModel extends \Illuminate\Database\Eloquent\Model
{

	protected $table = 'device_tokens';

	protected $app;
	public function __construct($app)
	{

		$this->app = $app;
	}
	private function makeFields($params = [])
	{

		$fields = [];
		if (array_key_exists('user_id', $params)) {
			$user_id = $this->app['helper']('Utility')->clearField($params['user_id']);
			$fields['user_id'] = $user_id;
		}

		if (array_key_exists('device_token', $params)) {
			$device_token = $this->app['helper']('Utility')->clearField($params['device_token']);
			$fields['device_token'] = $device_token;
		}

		if (array_key_exists('device_type', $params)) {
			$device_type = $this->app['helper']('Utility')->clearField($params['device_type']);
			$fields['device_type'] = $device_type;
		}
		return $fields;
	}
	public function addDeviceToken($params = [])
	{
		$payLoad = [];

		if (
			!isset($params['device_token']) ||
			(isset($params['device_token']) && !$this->app['helper']('Utility')->notEmpty($params['device_token'])) ||
			!isset($params['device_type']) ||
			(isset($params['device_type']) && !$this->app['helper']('Utility')->notEmpty($params['device_type']))
		) {
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'device_type, device_token'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code'=>400];
		} else {
			$fields = $this->makeFields($params);
			$fields['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
			
			if((isset($fields['user_id']) && $this->app['helper']('Utility')->notEmpty($fields['user_id']) && $this->app['load']('Models_UserModel')->existOneRow([['user_id','=',$fields['user_id']]]))) $fields['user_id'] = $fields['user_id']; else $fields['user_id'] = 0;
				
			if(!$this->app['load']('Models_DeviceTokenModel')->existOneRow([['device_token','=',$fields['device_token']]]))
				DeviceTokenModel::insertGetId($fields);
			else
				DeviceTokenModel::where([['device_token','=',$fields['device_token']]])->update($fields);
			$msg = $this->app['translator']->trans('add', array('%name%' => 'Device Token'));
			$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];
		}
		return $payLoad;
	}
	public function getDeviceTokenByUserId($idUser = '')
	{

		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($idUser)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {


			$select = ['device_token', 'user_id', 'device_type'];
			$where[] = ['user_id', '=', $idUser];
			$finddevice = $this->returnRows($where,$select);
			$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $finddevice];
			
		}

		return $payLoad;
	}
	
	public function getDeviceTokenByUserIds($userids = '')
	{

		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($userids)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {


			$select = ['device_token', 'user_id', 'device_type'];
			$arrayuserids = explode(',',$userids);
			$finddevice = DeviceTokenModel::select($select)->whereIn('user_id', $arrayuserids)->get();
			$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $finddevice];
			
		}

		return $payLoad;
	}
	
	public function existOneRow($where =[] , $select = ['id']){
		$res = false;
		if($this->app['helper']('Utility')->notEmpty($where)){
			$row = DeviceTokenModel::select($select)->where($where)->first();
			if($this->app['helper']('Utility')->notEmpty($row)){
				$res =  true;
			}
		}else{
			$rows = DeviceTokenModel::select($select)					
							->get();		
			if($this->app['helper']('Utility')->notEmpty($rows)){
				$res = true;
			}	
		}
		
		return $res;
		
	}
	
	public function returnRows( $where =[] , $select = ['*']){
		
		$this->app['helper']('ModelLog')->Log();
		
		$res = [];
		if($this->app['helper']('Utility')->notEmpty($where)){
			
			$rows = DeviceTokenModel::select($select)					
							->where($where)
							->get();		

		if($this->app['helper']('Utility')->notEmpty($rows)){
				$res =  $rows->toArray();
			}
			
		}else{
			$rows = DeviceTokenModel::select($select)					
							->get();		

			if($this->app['helper']('Utility')->notEmpty($rows)){
				$res =  $rows->toArray();
			}
			
		}
		return $res;
	}
	
	public function insert($details){
		
		$this->app['helper']('ModelLog')->Log();
		
		if($this->app['helper']('Utility')->notEmpty($details)){
			
			$saveId = DeviceTokenModel::insertGetId($details);

			return ['status'=>'Success','message'=>'','code'=>200, 'data'=>['saveId'=>$saveId]];
		}else{
			return ['status'=>'Error','message'=>"There is'nt data for insert . ", 'code'=>400];
		}
		
	}
	
	public function updateRows($update = [] , $where =[]){
		
		$this->app['helper']('ModelLog')->Log();
		
		if($this->app['helper']('Utility')->notEmpty($where) && $this->app['helper']('Utility')->notEmpty($update)){
			
			$rows = DeviceTokenModel::where($where)
							->update($update);
			return ['status'=>'Success','message'=>'','code'=>200];
		}else{
			return ['status'=>'Error','message'=>"There is'nt data for update . ", 'code'=>400];
		}
		
	}
	
	public function deleteRows($where =[]){
		
		DeviceTokenModel::where($where)->delete();
		$msg = $this->app['translator']->trans('delete', array('%name%' => 'DeviceToken'));
		return $payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];		
		
	}

}
