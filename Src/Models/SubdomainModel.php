<?php

namespace Models;

use Illuminate\Pagination\Paginator;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Query\Expression as raw;

class SubdomainModel extends \Illuminate\Database\Eloquent\Model
{

	protected $table = 'subdomain';

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

		if (array_key_exists('subdomain', $params)) {
			$subdomain = $this->app['helper']('Utility')->clearField($params['subdomain']);
			$fields['subdomain'] = $subdomain;
		}
		
		if (array_key_exists('company_name', $params)) {
			$company_name = $this->app['helper']('Utility')->clearField($params['company_name']);
			$fields['company_name'] = $company_name;
		}

		if (array_key_exists('title', $params)) {
			$title = $this->app['helper']('Utility')->clearField($params['title']);
			$fields['title'] = $title;
		}

		if (array_key_exists('keyword', $params)) {
			$keyword = $this->app['helper']('Utility')->clearField($params['keyword']);
			$fields['keyword'] = $keyword;
		}

		if (array_key_exists('description', $params)) {
			$description = $this->app['helper']('Utility')->clearField($params['description']);
			$fields['description'] = $description;
		}

		
		return $fields;
	}
	
	public function addSubdomain($params = [])
	{
		$payLoad = [];

		if (
			!isset($params['subdomain']) ||
			(isset($params['subdomain']) && !$this->app['helper']('Utility')->notEmpty($params['subdomain']))
		) {
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code'=>400];
		} else {
			$fields = $this->makeFields($params);
			$fields['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
			
			if((isset($fields['user_id']) && $this->app['helper']('Utility')->notEmpty($fields['user_id']) && $this->app['load']('Models_UserModel')->existOneRow([['user_id','=',$fields['user_id']]]))) $fields['user_id'] = $fields['user_id']; else $fields['user_id'] = 0;

			SubdomainModel::insertGetId($fields);

			$msg = $this->app['translator']->trans('add', array('%name%' => 'Subdamain'));
			$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];
		}
		return $payLoad;
	}
	
	
	public function existOneRow($where =[] , $select = ['id']){
		$res = false;
		if($this->app['helper']('Utility')->notEmpty($where)){
			$row = SubdomainModel::select($select)->where($where)->first();
			if($this->app['helper']('Utility')->notEmpty($row)){
				$res =  true;
			}
		}else{
			$rows = SubdomainModel::select($select)					
							->get();		
			if($this->app['helper']('Utility')->notEmpty($rows)){
				$res = true;
			}	
		}
		
		return $res;
		
	}
	
	public function findByname($subdomain = '')
	{

		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($subdomain)) {

			$select = ['id','user_id','company_name','subdomain','title','keyword','description'];
			$where[] = ['subdomain','=',$subdomain];
			$find = SubdomainModel::select($select)->where($where)->first();
			
			if($this->app['helper']('Utility')->notEmpty($find)){
				$find = $find->toArray();
				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $find];
			}else{
				$msg = $this->app['translator']->trans('404');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code'=>404];	
			}
			
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'subdomain'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}
	
	public function getSubdomainByUderId($user_id = '')
	{

		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($user_id)) {

			$select = ['id','user_id','company_name','subdomain','title','keyword','description'];
			$where[] = ['user_id','=',$user_id];
			$find = SubdomainModel::select($select)->where($where)->first();
			
			if($this->app['helper']('Utility')->notEmpty($find)){
				$find = $find->toArray();
				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $find];
			}else{
				$msg = $this->app['translator']->trans('404');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code'=>404];	
			}
			
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User Id'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}

	
	public function makeSubdomainName($subdomain,$companyname)
	{
		$companyname = $this->app['helper']('Utility')->clearField($companyname);
		$findLastId = SubdomainModel::select('id')->where([['company_name','=', $companyname]])->count();
		if (!empty($findLastId)) {
			$userCount = ++$findLastId;
		} else {
			return $subdomain;
		}
		return $subdomain.$userCount;
	}
	
	
	public function checkDuplicate($subdomain = '')
	{

		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($subdomain)) {

			$where[] = ['subdomain','=',$subdomain];
			$duplicates = SubdomainModel::select('user_id')->where($where)->first();
			
			if($this->app['helper']('Utility')->notEmpty($duplicates))
				$duplicates = $duplicates->toArray();
				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $duplicates];
			
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'subdomain'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}
	
	public function subdomainupdate($subdomainID, $params)
	{
		$payLoad = [];
		if ($this->app['helper']('Utility')->notEmpty($subdomainID)) {

			$fields = $this->makeFields($params);

			if($this->existOneRow([['id', '=', $subdomainID]])){
				SubdomainModel::where('id', '=', $subdomainID)->update($fields);
				$payLoad = $this->findById($subdomainID);
				if($payLoad['status']=='Success'){
					$msg = $this->app['translator']->trans('edit', array('%name%' => 'Company setting'));
					$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200 , 'data'=>$payLoad['data']];
				}
			}

			
		} else {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain Id'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

		return $payLoad;
	}
	
	
	
	
	public function findById($subdomainID)
	{
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($subdomainID)) {

			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain Id'));
			$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		} else {

			$select = ['id','user_id','company_name','subdomain','title','keyword','description'];
			$find = SubdomainModel::select($select)->where('id', '=', $subdomainID)->first();
			if (!empty($find)) { // user found

				$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $find->toArray()];
			} else { // user not found

				$msg = $this->app['translator']->trans('404', array());
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}

		return $payLoad;
	}

}
