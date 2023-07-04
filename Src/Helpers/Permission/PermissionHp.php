<?php
namespace Helper\Permission;

class PermissionHp{
	
	protected $app;
	
	public function __construct($app){		
		$this->app = $app;	
 
	}
	
	public function getAllAccess($request = []) {
		$payLoad = [];
		$res = $this->app['load']('Models_AccessPage')->returnRows();
		if(isset($res['permissions']) && $this->app['helper']('Utility')->notEmpty($res['permissions'])){
			$rst = json_decode($res['permissions']);
		}else{
			$rst = $this->app['translator']->trans('NotFound');
		}
		$payLoad = ['status'=>'success','result'=>$rst];
		return $payLoad;
	}
	
	
	public function getPermissions($request = [] ,$companyId) {
		$payLoad = [];
		
		$userId = $request->get('id_user');
		
		if($this->app['helper']('Utility')->notEmpty($userId)){
			
			$result = $this->app['load']('Component_oAuth_Models_CompanyDetails')->existOneCompany($userId , $companyId);
		
			$plansId = $result['id_plans'];
			$where[] = ['plans_id','=',$plansId];
			
			$res = $this->app['load']('Models_AccessPage')->existOneRow($where);
			
			if(isset($res['permissions']) && $this->app['helper']('Utility')->notEmpty($res['permissions'])){
				$rst = json_decode($res['permissions']);
			}else{
				
				$rst = $this->app['translator']->trans('NotFound');
			}
			
			
			$payLoad = ['status'=>'success','result'=>$rst];
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans('401')];
			
		}
		
		return $payLoad;
	}
	
	public function deletePermissions($request = [] ,$plansId){
		$payLoad = [];
		
		$where[] = ['plans_id','=',$plansId];
		$exist = $this->app['load']('Models_AccessPage')->existOneRow($where);

		if($this->app['helper']('Utility')->notEmpty($exist)){
			$where=[];
			$where[] = ['plans_id','=',$exist['plans_id']];
			$this->app['load']('Models_AccessPage')->deleteRows($where);
			$payLoad = ['status'=>'success','message'=>$this->app['translator']->trans('200')];
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans('NotFound')];
		}
		
		return $payLoad;
				
	}
	
	
	public function savePermissions($request = []){
		$payLoad = [];
		
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		
		$payLoad = $this->validateParams($postParameter);
		
		if($payLoad['status']==='error'){
			return $payLoad;
		}else{
			
			$where=[];
			$where[] = ['plans_id','=',$payLoad['result']['plans_id']];

			$exist = $this->app['load']('Models_AccessPage')->existOneRow($where);
			if($this->app['helper']('Utility')->notEmpty($exist)){
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => 'Identify'))];
			}else{
				$payLoad = $this->app['load']('Models_AccessPage')->insert($payLoad['result']);
				if($payLoad['status']==='success'){
					$msg = $this->app['translator']->trans('200');
					$payLoad['message'] = $msg ;
				}
			}
			
		}
		
		return $payLoad;
	
	}
	
	
	public function updatePermissions($request = [] ,$accessId){
		$payLoad = [];
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		$where[] = ['id','=',$accessId];
			
		$exist = $this->app['load']('Models_AccessPage')->existOneRow($where);
		if($this->app['helper']('Utility')->notEmpty($exist)){
			
			$payLoad = $this->validateUpdateParams($postParameter);
	
			if($payLoad['status']==='error'){
				return $payLoad;
			}else{
				$where = [];
				$where[] = ['id','=',$accessId];
				$payLoad = $this->app['load']('Models_AccessPage')->updateRows($payLoad['result'] , $where);
				if($payLoad['status']==='success'){
					$msg = $this->app['translator']->trans('200');
					$payLoad['message'] = $msg ;
				}
			}
			
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
			'NotFound')];
		}
		
		return $payLoad;
	}
	
	public function validateParams($params){
		$payLoad = [];
		$response  = [];
		if($this->app['helper']('ArrayFunc')->isArray($params) && $this->app['helper']('Utility')->notEmpty($params)){
			/*****************************/
			if(isset($params['plansId']) && $this->app['helper']('Utility')->notEmpty($params['plansId'])){
				
				$identify = $this->app['helper']('Utility')->secureInput($params['plansId']);
				$response['plans_id'] = $identify;
				$where=[];
				$where[] = ['identify','=',$identify];
			
				$existkey = $this->app['load']('Models_Plans')->existOneRow($where);
				if(!$this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' plansId parameter'))];
				}
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' plansId parameter'))];
			}
			/*****************************/
			
			if(isset($params['permissions'])){
				$params['permissions'] = trim($params['permissions']);
				
				if($this->app['helper']('Utility')->notEmpty($params['permissions']) && $this->app['helper']('Utility')->isJSON($params['permissions'])){

					$response['permissions'] = $params['permissions'];
 
				}elseif(strtolower($params['permissions'])=='null'){
					$response['permissions'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' permissions parameter'))];
				}
			}
			/*****************************/
			if(!isset($payLoad['status']) || $payLoad['status']!=='error'){
				
				$payLoad = ['status'=>'success','result'=>$response];
			}
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => 'all of them'))];
		}
		
		return $payLoad;
	}
	
	public function validateUpdateParams($params){
		$payLoad = [];
		$response  = [];
		if($this->app['helper']('ArrayFunc')->isArray($params) && $this->app['helper']('Utility')->notEmpty($params)){
			/*****************************/
			if(isset($params['plansId']) && $this->app['helper']('Utility')->notEmpty($params['plansId'])){
				
				$identify = $this->app['helper']('Utility')->secureInput($params['plansId']);
				$response['plans_id'] = $identify;
				$where=[];
				$where[] = ['identify','=',$identify];
			
				$existkey = $this->app['load']('Models_Plans')->existOneRow($where);
				if(!$this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' plansId parameter'))];
				}
				
			}
			/*****************************/
			
			if(isset($params['permissions'])){
				$params['permissions'] = trim($params['permissions']);
				
				if($this->app['helper']('Utility')->notEmpty($params['permissions']) && $this->app['helper']('Utility')->isJSON($params['permissions'])){

					$response['permissions'] = $params['permissions'];
 
				}elseif(strtolower($params['permissions'])=='null'){
					$response['permissions'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' permissions parameter'))];
				}
			}
			/*****************************/
			
			if(!isset($payLoad['status']) || $payLoad['status']!=='error'){
				
				$payLoad = ['status'=>'success','result'=>$response];
			}
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => 'all of them'))];
		}
		
		return $payLoad;
	}
	
		
}
	