<?php
namespace Component\Admin\Helpers;

use Component\Admin\Models\Role;

class RoleHp{
	
	protected $app;
	protected $roleModel;
	
	public function __construct($app){
		$this->app = $app;
		$this->roleModel = new Role($this->app);
    }
	
	public function newRole($request){
	
		$res = $this->roleModel->insert($request);
		return $res;
		
	}
	
	public function editRole($request){
		
		$idRole = $request->get('id');
		$res = $this->roleModel->edit($idRole,$request);
		
		return $res;
		
	}
	
	public function deleteRole($idRole){
		
		$delRole = $this->roleModel->deleteRole($idRole);
		return $delRole;
		
	}
	
	public function roleList($idCompany,$idUser){
		
		$list = $this->roleModel->roleList($idCompany,$idUser);
		
		return $list;
		
	}

	
	public function details($request){
		
		$idRole = $request->get('id');
		$idCompany = $request->get('id_company');
		
		$details = $this->roleModel->roleDetail($idRole,$idCompany);
		
		return $details;
		
	}
	
}