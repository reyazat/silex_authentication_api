<?php
namespace Component\Admin\Models;

use Helper\Utility;
use Helper\DateTimeFunc;
use Component\oAuth\Models\CompanyUser;


class Role extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'role';
	protected $app;
	protected $utility;
	
	public function __construct($app){
		
		$this->app = $app;
		$this->utility = new Utility();
		
    }
	
	private function makeFields($request){
		
		$fields = [];
		if($this->utility->notEmpty($request->get('role_name'))){
			$fields['role_name'] = $request->get('role_name');
		}
		
		if($this->utility->notEmpty($request->get('id_company'))){
			$fields['id_company'] = $request->get('id_company');
		}
		
		if($this->utility->notEmpty($request->get('id_user'))){
			$fields['id_user'] = $request->get('id_user');
		}
		
		if($this->utility->notEmpty($request->get('resources'))){
			$fields['resources'] = $request->get('resources');
		}
		
		$datTime = new DateTimeFunc();
		$fields['cdate'] = $datTime->nowDateTime();
		
		return $fields;
		
	}
	
	public function insert($request){
		
		$payLoad = [];
		$fields = self::makeFields($request);
		
		if(!$this->utility->notEmpty($fields['role_name']) || 
		   !$this->utility->notEmpty($fields['id_company']) || 
		   !$this->utility->notEmpty($fields['id_user']) ){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Role Name,Id Company,Id User).'];
			
		}else{
			
			$idRole = Role::insertGetId($fields);
		
			if(!$this->utility->notEmpty($idRole)){

				$this->app['monolog.debug']->error('error in add new role',$fields);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

			}else{

				$payLoad = ['status'=>'success','message'=>'Role added successfully.','id_role'=>$idRole];

			}
			
		}
		
		return $payLoad;
		
	}
	
	public function edit($idRole,$request){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idRole)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Role).'];
			
		}else{
			
			$fields = self::makeFields($request);
			
			if(!$this->utility->notEmpty($fields['role_name']) || 
			  !$this->utility->notEmpty($fields['id_company'])){

				$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Role Name,Id Company).'];

			}else{
				
				$updateId = Role::where('id', $idRole)
									->update($fields);
			
				if(!$this->utility->notEmpty($updateId)){

					$this->app['monolog.debug']->error('error in add new role',['id'=>$updateId,'details'=>$fields]);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

				}else{
					$CompanyUser = new CompanyUser($this->app);
					$users = $CompanyUser->getUserWithIdRole($idRole);
					
					foreach($users as $user){
						
						// add user to update menu list
						$menuBuilder = $this->app['helper']('MenuBuilder')->addToMenuSession($user['user_identify'],$user['id_company']);
						
					}

					$payLoad = ['status'=>'success','message'=>'Role updated successfully.','id_role'=>$idRole];

				}
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function deleteRole($idRole){
		
		$payLoad = [];
		
		if(!$this->utility->notEmpty($idRole)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Role).'];
			
		}else{
			
			$companuUserModel = new CompanyUser($this->app);
			$users = $companuUserModel->getUserWithIdRole($idRole);
			
			foreach($users as $user){
				
				$this->app['helper']('MenuBuilder')->addToMenuSession($user['id_company'],$user['user_identify']);
				
			}
			
			$deleteRole = Role::where('id','=',$idRole)->delete();
		
			if($this->utility->notEmpty($deleteRole)){

				$payLoad = ['status'=>'success','message'=>'Role deleted successfully'];

			}else{

				$this->app['monolog.debug']->error('error in delete role',['id role'=>$idRole]);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

			}
			
		}
		
		return $payLoad;
		
	}
	
	public function roleList($idCompany,$idUser){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idCompany) || 
		  !$this->utility->notEmpty($idUser)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(ID Company,ID User).'];
			
		}else{
			
			$roleList = Role::select('id','role_name','resources')
								->where('id_company','=',$idCompany)
								->where('id_user','=',$idUser)
								->get();
			$payLoad = $roleList->toArray();
			
		}
		return $payLoad;
		
	}
	

	public function roleDetail($id,$idCompany){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($id) ||
		  !$this->utility->notEmpty($idCompany)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Role,Id Company).'];
			
		}else{
			
			$res = Role::select('id','role_name','resources')
						->where('id','=',$id)
						->where('id_company','=',$idCompany)
						->get();
			
			if(isset($res[0]) && $this->utility->notEmpty($res[0])){
				$payLoad = $res[0]->toArray();
			}else{
				$payLoad = [];
			}
			
			
		}
		
		return $payLoad;
		
	}
	
}