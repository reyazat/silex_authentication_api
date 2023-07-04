<?php
namespace Helper\Setting;

class SettingHp{
	
	protected $app;
	
	public function __construct($app){		
		$this->app = $app;	
 
	}
	
	
	public function getSoftwares($request = []){
		$select = ["name","service_id","version","status","signout","success","picture","data_get","data_update"];
		$where = [];
		$name = '';
		$getParameter = $this->app['helper']('RequestParameter')->getParameter($request);
		if(isset($getParameter['name'])) $name = $getParameter['name'];
		
		if($this->app['helper']('Utility')->notEmpty($name)){
			$where[] = ['name','=',$name];
			$rows = $this->app['load']('Models_SoftwareDetails')->existOneRow($where , $select );
		}else{
			$rows = $this->app['load']('Models_SoftwareDetails')->returnRows($where , $select );
		}
		
		if($this->app['helper']('Utility')->notEmpty($rows)){
			
				$payLoad = ['status'=>'success','result'=>$rows];
				
			}else{
				$payLoad = ['status'=>'success','result'=>$this->app['translator']->trans('NotFound')];
			}
		return $payLoad;
	}
	
	
	public function saveSoftwares($request =[]) {
		
		$payLoad = [];
		
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		
		$payLoad = $this->validateParams($postParameter);
		
		if($payLoad['status']==='error'){
			return $payLoad;
		}else{
			
			$where=[];
			$where[] = ['service_id','=',$payLoad['result']['service_id']];

			$exist = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
			if($this->app['helper']('Utility')->notEmpty($exist)){
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => 'Identify'))];
			}else{
				$payLoad = $this->app['load']('Models_SoftwareDetails')->insert($payLoad['result']);
				if($payLoad['status']==='success'){
					$msg = $this->app['translator']->trans('200');
					$payLoad['message'] = $msg ;
				}
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function updateSoftwares($request =[],$id) {
		
		$payLoad = [];
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		$where[] = ['id','=',$id];
			
		$exist = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
		if($this->app['helper']('Utility')->notEmpty($exist)){
			
			$payLoad = $this->validateUpdateParams($postParameter);
	
			if($payLoad['status']==='error'){
				return $payLoad;
			}else{
				$where = [];
				$where[] = ['id','=',$id];
				$payLoad = $this->app['load']('Models_SoftwareDetails')->updateRows($payLoad['result'] , $where);
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
	
	public function delSoftwares($request =[] , $serviceId) {
		$payLoad = [];
		
		$where[] = ['service_id','=',$serviceId];
		$exist = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);

		if($this->app['helper']('Utility')->notEmpty($exist)){
			$where=[];
			$where[] = ['service_id','=',$exist['service_id']];
			$this->app['load']('Models_SoftwareDetails')->deleteRows($where);
			$payLoad = ['status'=>'success','message'=>$this->app['translator']->trans('200')];
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans('NotFound')];
		}
		
		return $payLoad;
		
	}
	
	public function validateParams($params){
		$payLoad = [];
		$response  = [];
		if($this->app['helper']('ArrayFunc')->isArray($params) && $this->app['helper']('Utility')->notEmpty($params)){
			/*****************************/
			if(isset($params['serviceId']) && $this->app['helper']('Utility')->notEmpty($params['serviceId'])){
				
				$serviceId = $this->app['helper']('Utility')->secureInput($params['serviceId']);
				$response['service_id'] = $serviceId;
				$where=[];
				$where[] = ['service_id','=',$serviceId];
			
				$existkey = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if($this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => ' serviceId parameter'))];
				}
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' serviceId parameter'))];
			}
			/*****************************/
			if(isset($params['name']) && $this->app['helper']('Utility')->notEmpty($params['name'])){
				
				$name = $this->app['helper']('Utility')->secureInput($params['name']);
				$response['name'] = $name;
				$where=[];
				$where[] = ['name','=',$name];
			
				$existkey = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if($this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => ' name parameter'))];
				}
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' name parameter'))];
			}
			/*****************************/
			if(isset($params['version'])){
				$params['version'] = trim($params['version']);
				
				if($this->app['helper']('Utility')->notEmpty($params['version']) && $params['version'] !== 'null'){

					$response['version'] = $params['version'];
 
				}elseif(strtolower($params['version'])=='null'){
					$response['version'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' version parameter'))];
				}
			}
			/*****************************/
			if(isset($params['status'])){
				$params['status'] = trim($params['status']);
				
				if($this->app['helper']('Utility')->notEmpty($params['status']) && $params['status'] !== 'null'){

					$response['status'] = $params['status'];
 
				}elseif(strtolower($params['status'])=='null'){
					$response['status'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' status parameter'))];
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
			if(isset($params['serviceId']) && $this->app['helper']('Utility')->notEmpty($params['serviceId'])){
				
				$serviceId = $this->app['helper']('Utility')->secureInput($params['serviceId']);
				$response['service_id'] = $serviceId;
				$where=[];
				$where[] = ['service_id','=',$serviceId];
			
				$existkey = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if($this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => ' serviceId parameter'))];
				}
				
			}
			/*****************************/
			if(isset($params['name']) && $this->app['helper']('Utility')->notEmpty($params['name'])){
				
				$name = $this->app['helper']('Utility')->secureInput($params['name']);
				$response['name'] = $name;
				$where=[];
				$where[] = ['name','=',$name];
			
				$existkey = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if($this->app['helper']('Utility')->notEmpty($existkey)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => ' name parameter'))];
				}
				
			}
			/*****************************/
			if(isset($params['version'])){
				$params['version'] = trim($params['version']);
				
				if($this->app['helper']('Utility')->notEmpty($params['version']) && $params['version'] !== 'null'){

					$response['version'] = $params['version'];
 
				}elseif(strtolower($params['version'])=='null'){
					$response['version'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' version parameter'))];
				}
			}
			/*****************************/
			if(isset($params['status'])){
				$params['status'] = trim($params['status']);
				
				if($this->app['helper']('Utility')->notEmpty($params['status']) && $params['status'] !== 'null'){

					$response['status'] = $params['status'];
 
				}elseif(strtolower($params['status'])=='null'){
					$response['status'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' status parameter'))];
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
	
