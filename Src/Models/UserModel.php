<?php

namespace Models;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Expression as raw;

class UserModel extends \Illuminate\Database\Eloquent\Model
{

	protected $table = 'users';

	protected $app;
	public function __construct($app)
	{
		$this->app = $app;
	}

	private function makeFields($params = [])
	{

		$fields = [];
		if (array_key_exists('firstname', $params)) {
			$fname = $this->app['helper']('Utility')->clearField($params['firstname']);
			$fname = $this->app['helper']('DataTable_TableInitField')->encryptFieldBase64($fname);
			$fields['firstname'] = $fname;
		}

		if (array_key_exists('lastname', $params)) {
			$lname = $this->app['helper']('Utility')->clearField($params['lastname']);
			$lname = $this->app['helper']('DataTable_TableInitField')->encryptFieldBase64($lname);
			$fields['lastname'] = $lname;
		}

		if (array_key_exists('username', $params)) {
			$userName = '';
			$userName = $this->app['helper']('Utility')->trm($params['username']);
			$userName = $this->app['helper']('DataTable_TableInitField')->encryptField($userName);
			$fields['username'] = $userName;
		}

		if (array_key_exists('password', $params)) {
			$password = '';
			$password = $this->app['helper']('Utility')->trm($params['password']);
			$fields['password'] = $this->app['helper']('CryptoGraphy')->encryptPassword($password);
		}

		if (array_key_exists('mobile_number', $params)) {
			$mobile = $this->app['helper']('Utility')->clearField($params['mobile_number']);
			$mobile = $this->app['helper']('DataTable_TableInitField')->encryptField($mobile);
			$fields['mobile_number'] = $mobile;
		}

		if (array_key_exists('date_of_birth', $params)) {
			$fields['date_of_birth'] = $this->app['helper']('Utility')->clearField($params['date_of_birth']);
		}
		
		if (array_key_exists('due_date', $params)) {
			$fields['due_date'] = $this->app['helper']('Utility')->clearField($params['due_date']);
		}
		
		if (array_key_exists('id_pack', $params)) {
			$fields['id_pack'] = $this->app['helper']('Utility')->clearField($params['id_pack']);
		}

		if (array_key_exists('address', $params)) {
			$address = $this->app['helper']('Utility')->clearField($params['address']);
			$address = $this->app['helper']('DataTable_TableInitField')->encryptFieldBase64($address);
			$fields['address'] = $address;
		}

		if (array_key_exists('gender', $params)) {
			$fields['gender'] = $this->app['helper']('Utility')->clearField($params['gender']);
		}

		if (array_key_exists('country', $params)) {
			$country = $this->app['helper']('Utility')->clearField($params['country']);
			// $country = $this->app['helper']('DataTable_TableInitField')->encryptFieldBase64($country);
			$fields['country'] = $country;
		}

		if (array_key_exists('roles', $params)) {
			$fields['roles'] = $this->app['helper']('Utility')->clearField($params['roles']);
		}

		if (array_key_exists('verified', $params)) {
			$fields['verified'] = $this->app['helper']('Utility')->clearField($params['verified']);
		}

		if (array_key_exists('unique_token', $params)) {
			$fields['unique_token'] = $this->app['helper']('Utility')->clearField($params['unique_token']);
		}
		
		if (array_key_exists('user_type', $params)) {
			$fields['user_type'] = $this->app['helper']('Utility')->clearField($params['user_type']);
		}
		
		if (array_key_exists('user_settings', $params)) {
			if ($this->app['helper']('Utility')->isJSON($params['user_settings'])) {
				$fields['user_settings'] = $params['user_settings'];
				// $fields['user_settings'] = $this->app['helper']('Utility')->decodeJson($params['user_settings']);

			} else
				$fields['user_settings'] = NULL;
		}

		if (array_key_exists('security_questions', $params)) {
			if ($this->app['helper']('Utility')->isJSON($params['security_questions'])) {
				$fields['security_questions'] = $params['security_questions'];

			} else
				$fields['security_questions'] = NULL;
		}


		return $fields;
	}

	private function makeUserIdentifier($username)
	{

		$userPerfix = substr($username, 0, 1);
		$userPerfix = ucfirst($userPerfix);

		$findLastId = UserModel::select('id')->orderBy('id', 'desc')->first();
		if (!empty($findLastId)) {
			$userCount = ++$findLastId->id;
		} else {
			$userCount = 1;
		}

		$userCount = sprintf('%04d', ($userCount));
		return $userPerfix . '_' . $userCount;
	}

	public function checkDuplicate($userName = '')
	{

		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($userName)) {

			$where = $this->app['helper']('DataTable_TableInitField')->searchEncryptField('username', $userName);
			$duplicates = UserModel::select('user_id')->whereRaw($where)->first();

			$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $duplicates];
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}

	public function signUp($params = [])
	{

		$payLoad = [];
		$fields = $this->makeFields($params);
		if (
			!isset($fields['username']) ||
			(isset($fields['username']) && !$this->app['helper']('Utility')->notEmpty($fields['username'])) ||
			!isset($fields['password']) ||
			(isset($fields['password']) && !$this->app['helper']('Utility')->notEmpty($fields['password']))
		) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username, Password'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$checkDuplicates = $this->checkDuplicate($params['username']);
			
			if ($checkDuplicates['status'] === 'Success') {

				if (count($checkDuplicates['data']) > 0) { // duplicate user

					$msg = $this->app['translator']->trans('exist', array('%name%' => 'Username'));
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 409];
				} else {
					
					$this->app['load']('Models_InviteModel')->removeInviteByEmail($params['username']);

					$fields['user_id'] = $this->makeUserIdentifier($params['username']);
					$getUuid = $this->app['helper']('CryptoGraphy')->createUUID();
					$fields['unique_token'] = str_replace('-', '', $getUuid['uuid']);
					$fields['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();

					$id = UserModel::insertGetId($fields);

					$msg = $this->app['translator']->trans('add', array('%name%' => 'User'));
					$payLoad = ['status' => 'Success', 'message' => $msg, 'code'=>200, 'data' => ['user_id'=>$fields['user_id']]];
				}
			} else { // error in check duplicate status

				$payLoad = $checkDuplicates;
			}
		}

		return $payLoad;
	}

	public function updateUserInfo($idUser = '', $params = [])
	{
		$payLoad = [];

		if ((isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password'])) ||
			!$this->app['helper']('Utility')->notEmpty($idUser)
		) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User, Password'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$fields = $this->makeFields($params);
			
			if($this->existOneRow([['user_id', '=', $idUser]])){	
				unset($fields['username']);
				unset($fields['user_id']);
				unset($fields['unique_token']);
				UserModel::where('user_id', '=', $idUser)->update($fields);
				$payLoad = $this->findById($idUser);
				if($payLoad['status']=='Success'){
					$msg = $this->app['translator']->trans('edit', array('%name%' => 'User'));
					$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200 , 'data'=>$payLoad['data']];
				}
			}			
		}

		return $payLoad;
	}

	public function findUserByEmail($username = '')
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($username)) { // username is empty

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['user_id', 'password','unique_token', 'user_type'];
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('firstname');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('lastname');
			$selectField[] = 'security_questions';

			$where = $this->app['helper']('DataTable_TableInitField')->searchEncryptField('username', $username);
			$findUser = UserModel::select($selectField)->whereRaw($where)->first();
			if (!empty($findUser)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $findUser->toArray()];
			} else { // user not found
				$msg = $this->app['translator']->trans('EmptyUser');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}

	public function removeUser($idUser = '')
	{

		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($idUser)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			UserModel::where('user_id', '=', $idUser)->delete();
			$payLoad = $this->getAllUser();
			if($payLoad['status']=='Success'){
				$msg = $this->app['translator']->trans('delete', array('%name%' => 'User'));
				$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200, 'data'=>$payLoad['data']];
			}
		}

		return $payLoad;
	}

	public function findById($idUser = '')
	{

		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($idUser)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['user_id','date_of_birth', 'gender', 'follower_count', 'following_count', 'country', 'user_settings' , 'user_type','id_pack','due_date'];

			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('firstname');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('lastname');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('address');
			// $selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('country');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('mobile_number');
			$selectField[] = 'security_questions';
			$findUser = UserModel::select($selectField)->where('user_id', '=', $idUser)->first();
			
			if (!empty($findUser)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $findUser->toArray()];
			} else { // user not found

				$msg = $this->app['translator']->trans('EmptyUser', array());
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}
	
	
	public function findByIds($usersids = '')
	{
		$payLoad = [];
		$selectField = ['id','user_id',  'user_type', 'follower_count'];

		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('firstname');
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('lastname');
		$arrayuserids = explode(',',$usersids);
		$findUsers = UserModel::select($selectField)->whereIn('user_id', $arrayuserids)->get();
		if(!empty($findUsers)){ // user found

			$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $findUsers->toArray()];
		} else { // user not found

			$msg = $this->app['translator']->trans('EmptyUser', array());
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
		}

		return $payLoad;
	}
	
	public function findUserByToken($token)
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($token)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Token'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$selectField = ['user_id','date_of_birth', 'gender', 'follower_count', 'following_count', 'country', 'user_settings' , 'user_type','id_pack','due_date'];

			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('firstname');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('lastname');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('address');
			// $selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('country');
			$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('mobile_number');
			$selectField[] = 'security_questions';
			$findUser = UserModel::select($selectField)->where('unique_token', '=', $token)->first();
			if (!empty($findUser)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $findUser->toArray()];
			} else { // user not found

				$msg = $this->app['translator']->trans('EmptyUser', array());
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}
	public function getAllUser($params = [])
	{

		$paginate = (isset($params['paginate']) ) ? (bool) $params['paginate'] : false;
		$currentPage = (isset($params['currentPage']) && !empty($params['currentPage'])) ? $params['currentPage'] : 0;
		$length = (isset($params['length']) && !empty($params['length'])) ? $params['length'] : 10;
		$orderBy = (isset($params['orderBy']) && !empty($params['orderBy'])) ? $params['orderBy'] : 'userid';
        $sortType = (isset($params['sortType']) && !empty($params['sortType'])) ? $params['sortType'] : 'asc';
		if($paginate){
			Paginator::currentPageResolver(function () use ($currentPage) {
				return $currentPage;
			});
		}

		$selectField = ['id','user_id',  'user_type', 'follower_count'];
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptField('username');
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('firstname');
		$selectField[] = $this->app['helper']('DataTable_TableInitField')->decryptFieldBase64('lastname');
		if($orderBy == 'userid')$orderBy ='id'; elseif($orderBy == 'firstname')$orderBy ='firstname'; elseif($orderBy == 'lastname')$orderBy ='lastname'; elseif($orderBy == 'user_type')$orderBy ='user_type'; else $orderBy = 'id';
		
		$fetch = UserModel::select($selectField);
		if(isset($params['user_type']) && !empty($params['user_type'])){
			$fetch = $fetch->where('user_type','=',$params['user_type']);
		}

		$fetch = $fetch->where('user_id','<>',$this->app['oauth']['id_user']);
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
	
	public function existOneRow($where =[] , $select = ['id']){
		$res = false;
		
		$row = UserModel::select($select)->where($where)->first();
		if($this->app['helper']('Utility')->notEmpty($row)){
			$res =  true;
		}
		
		
		return $res;
		
	}

}
