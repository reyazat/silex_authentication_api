<?php
namespace Models;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Expression as raw;

class InviteModel extends \Illuminate\Database\Eloquent\Model{

	protected $table = 'invite_user';
	protected $app;
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeFields($params = [])
	{

		$fields = [];
		
		if (array_key_exists('username', $params)) {
			$userName = '';
			$userName = $this->app['helper']('Utility')->trm($params['username']);
			$userName = $this->app['helper']('DataTable_TableInitField')->encryptField($userName);
			$fields['username'] = $userName;
		}
		if (array_key_exists('status', $params)) {
			$fields['status'] = $this->app['helper']('Utility')->clearField($params['status']);
		}

		if (array_key_exists('unique_code', $params)) {
			$fields['unique_code'] = $this->app['helper']('Utility')->clearField($params['unique_code']);
		}
		
		if (array_key_exists('user_type', $params)) {
			$fields['user_type'] = $this->app['helper']('Utility')->clearField($params['user_type']);
			$fields['user_type'] = strtolower($fields['user_type']);
			$fields['user_type'] = ucfirst($fields['user_type']);
		}

		if (array_key_exists('relation', $params)) {
			$relation = $this->app['helper']('Utility')->clearField($params['relation']);
			if($this->app['helper']('Utility')->notEmpty($relation)) $fields['relation'] = $relation;
		}

		return $fields;
	}


	public function addInvite($params = [])
	{

		$payLoad = [];
		$fields = $this->makeFields($params);
		if (
			!isset($fields['username']) ||
			(isset($fields['username']) && !$this->app['helper']('Utility')->notEmpty($fields['username'])) ||
			!isset($fields['user_type']) ||
			(isset($fields['user_type']) && !$this->app['helper']('Utility')->notEmpty($fields['user_type']))
		) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username, User Type'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$checkDuplicates = $this->checkDuplicate($params['username']);
			
			if ($checkDuplicates['status'] === 'Success') {
				if (isset($checkDuplicates['data']) &&  !empty($checkDuplicates['data'])) { // duplicate user
					$msg = $this->app['translator']->trans('ExistInvite');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 409];
				} else {
					$checkDuplicates = $this->app['load']('Models_UserModel')->checkDuplicate($params['username']);
					
					if ($checkDuplicates['status'] === 'Success' && !empty($checkDuplicates['data']) && is_array($checkDuplicates['data'])) {
						$msg = $this->app['translator']->trans('ExistSame');
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 409];
					
					}else{
						$fields['unique_code'] = $this->app['helper']('CryptoGraphy')->md5EncryptHash($this->app['helper']('CryptoGraphy')->randomPassword().$this->app['helper']('DateTimeFunc')->nowDateTime());
						if(isset($fields['user_type']) && $fields['user_type'] == 'Admin'){
							$fields['user_type'] == 'Owner';
						}
						$fields['owner_id'] = $this->app['oauth']['id_user'];
						$fields['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();

						InviteModel::insertGetId($fields);
						$this->app['cache']->store('inviteMail_'.$params['username'],$fields['unique_code'], 1750);
						$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => ['unique_code'=>$fields['unique_code']]];
					}
				}
			} else { // error in check duplicate status

				$payLoad = $checkDuplicates;
			}
		}

		return $payLoad;
	}
	
	public function updateInvite($inviteid, $params = [])
	{
		$payLoad = [];

		$fields = $this->makeFields($params);
		
		if($this->existOneRow([['id', '=', $inviteid]])){
			unset($fields['username']);
			unset($fields['owner_id']);
			unset($fields['unique_code']);
			if(isset($fields['user_type']) && $fields['user_type'] == 'Admin'){
				$fields['user_type'] == 'Owner';
			}
			InviteModel::where('id', '=', $inviteid)->update($fields);
			$payLoad = $this->findById($inviteid);
			if($payLoad['status']=='Success'){
				$msg = $this->app['translator']->trans('edit', array('%name%' => 'Invited User'));
				$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200 , 'data'=>$payLoad['data']];
			}
		}
		return $payLoad;
	}
	
	public function findById($inviteid)
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($inviteid)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Invite Id'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['id','user_type', 'unique_code' ,'status','owner_id', 'relation','created_at'];
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');

			$find = InviteModel::select($selectField)->where('id', '=', $inviteid)->first();
			if (!empty($find)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $find->toArray()];
			} else { // user not found

				$msg = $this->app['translator']->trans('EmptyUser', array());
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}
	
	public function findByCode($code)
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($code)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Invite Code'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['id','user_type', 'unique_code' ,'status','owner_id', 'relation' ,'created_at'];
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');

			$find = InviteModel::select($selectField)->where('unique_code', '=', $code)->first();
			if (!empty($find)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $find->toArray()];
			} else { // user not found
				$msg = $this->app['translator']->trans('EmptyUser', array());
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}
		return $payLoad;
	}
	
	public function removeInvite($inviteid)
	{
		$payLoad = [];
		if($this->existOneRow([['id', '=', $inviteid]])){
			InviteModel::where('id', '=', $inviteid)->delete();
			$payLoad = $this->getAllInvite();
			if($payLoad['status']=='Success'){
				$msg = $this->app['translator']->trans('delete', array('%name%' => 'Invited User'));
				$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200, 'data'=>$payLoad['data']];
			}
		}

		return $payLoad;
	}

	public function removeInviteByEmail($username)
	{
		$payLoad = [];
		$payLoad = $this->findUserByEmail($username);
		if($payLoad['status']=='Success'){
			$where = $this->app['helper']('DataTable_TableInitField')->searchEncryptField('username', $username);
			InviteModel::whereRaw($where)->delete();
			if($payLoad['status']=='Success'){
				$msg = $this->app['translator']->trans('delete', array('%name%' => 'Invited User'));
				$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];
			}
		}

		return $payLoad;
	}
	
	public function getAllInvite($params = [])
	{
		$paginate = (isset($params['paginate']) ) ? (bool) $params['paginate'] : false;
		$currentPage = (isset($params['currentPage']) && !empty($params['currentPage'])) ? $params['currentPage'] : 0;
		$length = (isset($params['length']) && !empty($params['length'])) ? $params['length'] : 10;
		$orderBy = (isset($params['orderBy']) && !empty($params['orderBy'])) ? $params['orderBy'] : 'id';
        $sortType = (isset($params['sortType']) && !empty($params['sortType'])) ? $params['sortType'] : 'Desc';
		if($paginate){
			Paginator::currentPageResolver(function () use ($currentPage) {
				return $currentPage;
			});
		}

		$selectField = ['id','user_type', 'status','created_at'];
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
	
		$selectField[] = new raw("(select FROM_BASE64(CAST(AES_DECRYPT(`company_name`,UNHEX(SHA2('".$this->app['config']['parameters']['mysql_params']['key']."',512))) as CHAR(512))) `companyname` from ap_users where user_id = ap_invite_user.owner_id) as companyname");
				
		$fetch = InviteModel::select($selectField);
		if(isset($params['user_type']) && !empty($params['user_type'])){
			$fetch = $fetch->where('user_type','=',$params['user_type']);
		}
		$fetch = $fetch->where('owner_id','=',$this->app['oauth']['id_user']);
		$fetch = $fetch->orderBy($orderBy, $sortType);
		if($paginate){
			$paginateResult = $fetch->paginate($length)->toArray();
			if(!empty($paginateResult))
				$returnResult = $paginateResult['data'] ;
			else
				$returnResult = [];
			
		}else{
			$returnResult = $fetch->get();
			if(!empty($returnResult))
				$returnResult = $returnResult->toArray() ;
			else
				$returnResult = [];
		}

		$result = [];
		if($paginate){
			$result['total'] = $paginateResult['total'];
			$result['currentPage'] = $paginateResult['current_page'];
			$result['lastPage'] = $paginateResult['last_page'];
			$result['length'] = $paginateResult['per_page'];
		}
		
		return ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $returnResult, 'pagination'=>$result];
	}
	public function findUserByEmail($username = '')
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($username)) { // username is empty

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['id','user_type', 'unique_code' ,'status','owner_id', 'relation','created_at'];
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');

			$where = $this->app['helper']('DataTable_TableInitField')->searchEncryptField('username', $username);
			$findUser = InviteModel::select($selectField)->whereRaw($where);
			$findUser = $findUser->first();
			
			if (!empty($findUser)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $findUser->toArray()];
			} else { // user not found
				$msg = $this->app['translator']->trans('EmptyUser');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}
	
	public function existOneRow($where =[] , $select = ['id']){
		$res = false;
		
		$row = InviteModel::select($select)->where($where)->first();
		if($this->app['helper']('Utility')->notEmpty($row)){
			$res =  true;
		}
		
		
		return $res;
		
	}
	
	private function checkDuplicate($userName)
	{

		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($userName)) {

			$where = $this->app['helper']('DataTable_TableInitField')->searchEncryptField('username', $userName);
			$duplicates = InviteModel::select('unique_code')->whereRaw($where);
			$duplicates = $duplicates->first();
			
			if($this->app['helper']('Utility')->notEmpty($duplicates))
				$duplicates = $duplicates->toArray();
				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $duplicates];
			
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}
	
}