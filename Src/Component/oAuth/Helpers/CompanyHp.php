<?php
namespace Component\oAuth\Helpers;

use Component\oAuth\Models\CompanyDetails;
use Component\oAuth\Models\CompanyUser;
use Component\oAuth\Models\SoftwareUser;
use Component\oAuth\Models\InviteUser;

use Component\Admin\Helpers\PackHp;


class CompanyHp{
	protected $app;
	protected $CompanyDetails;
	protected $CompanyUser;
	protected $SoftwareUser;
	
	public function __construct($app){
		$this->app = $app;
		$this->CompanyDetails = new CompanyDetails($this->app);
		$this->CompanyUser = new CompanyUser($this->app);
		$this->SoftwareUser = new SoftwareUser($this->app);
    }
	private function companyHouseFields($companyDetails){

		$details = [];
		
		if(isset($companyDetails['company_name']) && $this->app['helper']('Utility')->notEmpty($companyDetails['company_name'])){
			$details['company_name'] = $companyDetails['company_name'];
		}
		//$details['no_of_employee'] = $companyDetails[''];
		if(isset($companyDetails['company_status']) && $this->app['helper']('Utility')->notEmpty($companyDetails['company_status'])){
			$details['company_status'] = $companyDetails['company_status'];
		}
		
		if(isset($companyDetails['company_number']) && $this->app['helper']('Utility')->notEmpty($companyDetails['company_number'])){
			$details['register_no'] = $companyDetails['company_number'];
		}
		//$details['industry'] = $companyDetails[''];
		$details['client_acquired'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
		
		if(isset($companyDetails['registered_office_address']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address'])){
			if(isset($companyDetails['registered_office_address']['country']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address']['country'])){
				
				$details['country'] = $companyDetails['registered_office_address']['country'];
				
			}
		}
		
		if(isset($companyDetails['registered_office_address']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address'])){
			if(isset($companyDetails['registered_office_address']['locality']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address']['locality'])){
				
				$details['locality'] = $companyDetails['registered_office_address']['locality'];
				
			}
		}
		
		if(isset($companyDetails['registered_office_address']) && $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address'])){
			if(isset($companyDetails['registered_office_address']['address_line_1']) && $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address']['address_line_1'])){
				
				$details['address1'] = $companyDetails['registered_office_address']['address_line_1'];
				
			}
		}
		
		if(isset($companyDetails['registered_office_address']) && $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address'])){
			if(isset($companyDetails['registered_office_address']['address_line_2']) && $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address']['address_line_2'])){
				
				$details['address2'] = $companyDetails['registered_office_address']['address_line_2'];
				
			}
		}
		
		if(isset($companyDetails['registered_office_address']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address'])){
			if(isset($companyDetails['registered_office_address']['postal_code']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['registered_office_address']['postal_code'])){
				
				$details['postcode'] = $companyDetails['registered_office_address']['postal_code'];
				
			}
		}
		
		if(isset($companyDetails['sic_codes']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['sic_codes'])){
			
			$details['sic_codes'] = implode(',',$companyDetails['sic_codes']);
			
		}
		
		if(isset($companyDetails['can_file']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['can_file'])){
			
			$details['can_file'] = $companyDetails['can_file'];
			
		}
		
		
		if(isset($companyDetails['date_of_cessation']) &&  $this->app['helper']('Utility')->notEmpty($companyDetails['date_of_cessation'])){
		
			$details['date_of_cessation'] = $companyDetails['date_of_cessation'];
			
		}
		
		return $details;
		
	}
	
	public function saveCompany($request){
		
		$payLoad = [];
		$companyName = $request->get('company_name');
		$details['company_name'] = $this->app['helper']('Utility')->clearField($companyName);			   
		$details['register_no'] = $request->get('register_no');
		
		if(isset($details['register_no']) &&  $this->app['helper']('Utility')->notEmpty($details['register_no'])){
			
			$resCompanyDetails = $this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['companyhouse'].'company/'.$details['register_no']);
		
			if($this->app['helper']('Utility')->is_set($resCompanyDetails['status']) && $resCompanyDetails['status'] == 'success'){
				
				$details = [];
				$details = self::companyHouseFields($resCompanyDetails['item']);

			}
			
		}
		
		// this field post from setting add company section
		if($this->app['helper']('Utility')->notEmpty($request->get('no_of_employee'))){
			$details['no_of_employee'] = $request->get('no_of_employee');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('address1'))){
			$details['address1'] = $request->get('address1');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('address2'))){
			$details['address2'] = $request->get('address2');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('country'))){
			$details['country'] = $request->get('country');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('locality'))){
			$details['locality'] = $request->get('locality');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('postcode'))){
			$details['postcode'] = $request->get('postcode');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('industry'))){
			$details['industry'] = $request->get('industry');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('timezone'))){
			$details['timezone'] = $request->get('timezone');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('website'))){
			$details['website'] = $request->get('website');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('fax'))){
			$details['fax'] = $request->get('fax');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('phone'))){
			$details['phone'] = $request->get('phone');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('email'))){
			$details['company_email'] = $request->get('email');
		}
		if($this->app['helper']('Utility')->notEmpty($request->get('contact_name'))){
			$details['contact_name'] = $request->get('contact_name');
		}
		
		$paymentstatus = $request->get('payment_status');
		if($this->app['helper']('Utility')->notEmpty($paymentstatus) && ( $paymentstatus== 'Lifetime' || $paymentstatus=='Trial' )){
			
			$details['payment_status'] = $paymentstatus;
			
			if($details['payment_status']=='Lifetime'){
				$details['id_plans'] = 'SPA28qoN';
				$details['due_date'] = $this->app['helper']('DateTimeFunc')->date_add(date('Y-m-d') , 30 , 'Y-m-d H:i:s');
			}else{
				$details['id_plans'] = 'UYL893Co';
				$details['due_date'] = $this->app['helper']('DateTimeFunc')->date_add(date('Y-m-d') , 14 , 'Y-m-d H:i:s');
			}
			
		}
		
		$details['active_from'] = $request->get('active_from');

		/*$pack = new PackHp($this->app);
		$packDetails = $pack->packDetailsByName('Free');
		$details['id_packs'] = $packDetails['id'];*/
		
		$idUser = $request->get('id_user');
		$idCompany = $this->CompanyDetails->addCompany($details);
		
		if(isset($idCompany['status']) &&  $idCompany['status'] == 'error'){
			$payLoad = $idCompany;
		}else{
			
			$this->app['helper']('OutgoingRequest')->postRequest($this->app['config']['webservice']['crm'].'system/firstsetup',[],['id'=>$idCompany,'id_user'=>$idUser]);

			$payLoad = $this->CompanyUser->addUserCompany(['id_company'=>$idCompany,
										'company_name'=>$details['company_name'],
										'user_identify'=>$idUser,
										'user_type'=>'SuperAdmin',
										'maker_identify'=>$idUser,
										//'id_packs'=>''/*$packDetails['id']*/,
										'cdate'=>$this->app['helper']('DateTimeFunc')->nowDateTime()]);
			
		}
		
		return $payLoad;
		
	}
	
	public function editCompanyDetails($request){
		
		$payLoad = [];
		
		$idUser = $request->get('id_user');
		$idCompany = $request->get('id_company');
		
		if(!$this->app['helper']('Utility')->notEmpty($idUser) ||
		  !$this->app['helper']('Utility')->notEmpty($idCompany)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id User,Id Company).'];
			
		}else{
			
			$findCompany = $this->SoftwareUser->getUserinfo($idUser,$idCompany);
			if($findCompany['maker_identify'] == $idUser){
				
				if($this->app['helper']('Utility')->notEmpty($request->get('company_name'))){
					$details['company_name'] = $request->get('company_name');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('register_no'))){
					$details['register_no'] = $request->get('register_no');
				}

				if(isset($details['register_no']) &&  $this->app['helper']('Utility')->notEmpty($details['register_no'])){

					$CompanyDetails = \Requests::get($this->app['config']['webservice']['companyhouse'].'company/'.$details['register_no']);
					$resCompanyDetails = $this->app['helper']('Utility')->decodeJson($CompanyDetails->body);

					if($this->app['helper']('Utility')->is_set($resCompanyDetails['status']) && $resCompanyDetails['status'] == 'success'){

						$details = [];
						$details = self::companyHouseFields($resCompanyDetails['item']);

					}

				}

				// this field post from setting add company section
				if($this->app['helper']('Utility')->notEmpty($request->get('no_of_employee'))){
					$details['no_of_employee'] = $request->get('no_of_employee');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('address1'))){
					$details['address1'] = $request->get('address1');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('address2'))){
					$details['address2'] = $request->get('address2');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('country'))){
					$details['country'] = $request->get('country');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('locality'))){
					$details['locality'] = $request->get('locality');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('postcode'))){
					$details['postcode'] = $request->get('postcode');
				}

				if($this->app['helper']('Utility')->notEmpty($request->get('industry'))){
					$details['industry'] = $request->get('industry');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('active_from'))){
					$details['active_from'] = $request->get('active_from');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('first_setup'))){
					$details['first_setup'] = $request->get('first_setup');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('timezone'))){
					$details['timezone'] = $request->get('timezone');
				}
				
				if($this->app['helper']('Utility')->notEmpty($request->get('id_plans'))){
					$details['id_plans'] = $request->get('id_plans');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('users_count'))){
					$details['users_count'] = $request->get('users_count');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('payment_status'))){
					$details['payment_status'] = $request->get('payment_status');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('due_date'))){
					$details['due_date'] = $request->get('due_date');
				}
				if($request->get('pre_defined_reason') !== null && $this->app['helper']('Utility')->notEmpty($request->get('pre_defined_reason'))){
					$details['pre_defined_reason'] = $request->get('pre_defined_reason');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('api_key'))){
					$details['api_key'] = $request->get('api_key');
				}
				if($this->app['helper']('Utility')->notEmpty($request->get('integrate_with_accounting'))){
					$details['integrate_with_accounting'] = $request->get('integrate_with_accounting');
				}
				if($request->get('integrate_details') !== null){
					$details['integrate_details'] = $request->get('integrate_details');
				}
				if($request->get('fm_perms') !== null){
					$details['fm_perms'] = $request->get('fm_perms');
				}
				$payLoad = $this->CompanyDetails->editCompany($idCompany,$details);
				
			}else{
				$payLoad = ['status'=>'error','message'=>'Sorry,you have not permission to edit this company.'];
			}

		}
		
		return $payLoad;
		
	}
	
	public function addUserToCompany($request){
		
		$details = [];
		$details['id_company'] = $request->get('id_company');
		$details['user_identify'] = $request->get('id_user');
		$details['user_type'] = $request->get('user_type');
		$details['maker_identify'] = $request->get('id_maker');
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($details['id_company']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['user_identify']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['user_type']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['maker_identify'])){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty.'];
			
		}else{
			
			// check user not be superadmin
			if($details['maker_identify'] != $details['user_identify']){
				
				$userDetail = $this->SoftwareUser->getUserByIdentify($details['user_identify']);
				
				$inviteUserModel = new InviteUser($this->app);
				$inviteDetail = $inviteUserModel->getInviteByEmail($userDetail['email'],$details['id_company']);
				
				$res = $this->SoftwareUser->getUserinfo($details['maker_identify'],$details['id_company']);
				
				if($this->app['helper']('Utility')->notEmpty($inviteDetail['id_role'])){
					
					$getRoleDetail = $this->app['helper']('HandlleRequest')->returnResult('/admin/role',
																					   'POST',
																					  ['id'=>$inviteDetail['id_role'],
																					  'id_company'=>$details['id_company'],
																					  'action'=>'details']);
					
					$roleDetail = $this->app['helper']('Utility')->convertResponseToArray($getRoleDetail);
					
					//$details['id_role'] = $inviteDetail['id_role'];
					$details['resources'] = $roleDetail['resources'];
					
					
				}else{
					
					//$details['id_role'] = $res['id_role'];
					$details['resources'] = $res['resources'];
					
				}
				
				//$details['id_packs'] = $res['id_packs'];
				
			}
			
			$companyDetails = self::companyInfo($details['id_company']);
			$details['company_name'] = $companyDetails['company_name'];
			$details['cdate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
		
			$payLoad = $this->CompanyUser->addUserCompany($details);
			
		}
		
		return $payLoad;
		
	}
	
	public function getlistOfCompany($idUser){
		
		$res = $this->CompanyUser->companyList($idUser);
		if(isset($res[0]['timezone'])){
			foreach($res as $i=>$row){
				$origin_dtz = new \DateTimeZone($row['timezone']);
				$remote_dtz = new \DateTimeZone('UTC');
				$origin_dt = new \DateTime("now", $origin_dtz);
				$remote_dt = new \DateTime("now", $remote_dtz);
				$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
				$res[$i]['offset'] = $offset;
			}
		}
		
		return $res;
		
	}
	
	public function companyInfo($idCompany){
		
		$res = $this->CompanyDetails->companyInfo($idCompany);
		return $res;
		
	}
	
	public function companyInfoByApiKey($apiKey){
		
		$res = $this->CompanyDetails->companyInfoByApiKey($apiKey);
		return $res;
		
	}
	
	public function companyAllUserList($idCompany){
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany)){
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company).'];
		}else{
			$payLoad = $this->CompanyUser->allUserList($idCompany);
		}
		
		return $payLoad;
		
	}
	
	public function companyUserList($idCompany,$IdUser){
		
		if($this->app['helper']('Utility')->notEmpty($idCompany) ||
		  $this->app['helper']('Utility')->notEmpty($IdUser)){
			
			$userInfo = $this->SoftwareUser->getUserinfo($IdUser,$idCompany);
			$userStatus = $userInfo['user_type'];
			
			switch($userStatus){
				
				case'SuperAdmin' :
					
					$userList = $this->CompanyUser->userList($idCompany,$IdUser,[]);
					$payLoad = $userList;
					
				break;
					
				case'Admin' :
					
					$userList = $this->CompanyUser->userList($idCompany,$IdUser,['company_user.maker_identify'=>$IdUser]);
					$payLoad = $userList;
					
				break;
					
				default: $payLoad = ['status'=>'error','message'=>'Sorry!you have not permission to access users list.'];
					
			}
			
			
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'Some require fields are empty(Id Company,Id User).'];
			
		}
		
		return $payLoad;
		
	}
	
	public function deleteEmployeeFromCompany($idEmployee,$idUser,$idCompnay){
		
		$userDetails = $this->SoftwareUser->getUserinfo($idUser,$idCompnay);
		$employeeDetails = $this->SoftwareUser->getUserinfo($idEmployee,$idCompnay);
		$InviteUser = new InviteUser($this->app);
		
		if($userDetails['user_type'] === 'SuperAdmin'){
			
			$payLoad = $this->CompanyUser->deleteUser($idEmployee,$idCompnay);
			// delete if user come from invite list
			$InviteUser->removeFromInviteList($idCompnay,$employeeDetails['email']);
			
			
		}else if($userDetails['user_type'] === 'Admin'){
			
			if($userDetails['user_identify'] == $employeeDetails['user_identify']){
				
				$payLoad = $this->CompanyUser->deleteUser($idEmployee,$idCompnay);
				// delete if user come from invite list
				$InviteUser->removeFromInviteList($idCompnay,$employeeDetails['email']);
				
			}else{
				
				$this->app['monolog.debug']->error('error in delete user from company list',['id user'=>$idUser,
																					   'id employee'=>$idEmployee,
																					   'id company'=>$idCompnay]);
				
				$payLoad = ['status'=>'error','message'=>'Sorry!You have not permission to delete user.'];
				
			}
			
		}else{
			
			$this->app['monolog.debug']->error('error in delete user from company list.',['id user'=>$idUser,
																					   'id employee'=>$idEmployee,
																					   'id company'=>$idCompnay]);
			
			$payLoad = ['status'=>'error','message'=>'Sorry!You have not permission to delete user.'];
			
		}
		
		return $payLoad;
		
	}
	
	public function updateInterface($request){
		
		$idCompany = $request->get('id_company');
		$idUser = $request->get('id_user');
		$idEmployee = $request->get('id_employee');
		$details = $request->get('data');

		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) ||
		  !$this->app['helper']('Utility')->notEmpty($idUser) ||
		  !$this->app['helper']('Utility')->notEmpty($idEmployee) ||
		  !$this->app['helper']('Utility')->notEmpty($details)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company,Id User,Id Employee,Edit fields).'];
			
		}else{
			
			$userDetails = $this->SoftwareUser->getUserinfo($idUser,$idCompany);
			$payLoad = $this->CompanyUser->editUser($idEmployee,$idCompany,$details);

		}
		
		return $payLoad;
		
	}
	
	
	public function updateUserCompany($request){
		
		$idCompany = $request->get('id_company');
		$idUser = $request->get('id_user');
		$idEmployee = $request->get('id_employee');
		$details = $request->get('data');

		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) ||
		  !$this->app['helper']('Utility')->notEmpty($idUser) ||
		  !$this->app['helper']('Utility')->notEmpty($idEmployee) ||
		  !$this->app['helper']('Utility')->notEmpty($details)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company,Id User,Id Employee,Edit fields).'];
			
		}else{
			
			$userDetails = $this->SoftwareUser->getUserinfo($idUser,$idCompany);
			$employeeDetails = $this->SoftwareUser->getUserinfo($idEmployee,$idCompany);
			
			if($userDetails['user_type'] === 'SuperAdmin'){
			
				if(isset($details['status']) && $details['status'] == 'Suspend'){ //suspend user
					$payLoad = $this->CompanyUser->suspendUser($idEmployee,$idCompany,$details);
				}else{
					$payLoad = $this->CompanyUser->editUser($idEmployee,$idCompany,$details);
				}
				
				
			}else if($userDetails['user_type'] === 'Admin'){
				
				if($userDetails['user_identify'] == $employeeDetails['maker_identify']){
					
					if(isset($details['status']) && $details['status'] == 'Suspend'){ //suspend user
						$payLoad = $this->CompanyUser->suspendUser($idEmployee,$idCompany,$details);
					}else{
						$payLoad = $this->CompanyUser->editUser($idEmployee,$idCompany,$details);
					}
		
				}else{
					
					$this->app['monolog.debug']->error('error in delete user from company list',['id user'=>$idUser,
																					   'id employee'=>$idEmployee,
																					   'id company'=>$idCompany,
																					   'details'=>$details]);
				
					$payLoad = ['status'=>'error','message'=>'Sorry!You have not permission to edit user.'];
					
				}
				
			}else{
				
				$this->app['monolog.debug']->error('error in delete user from company list.',['id user'=>$idUser,
																					   'id employee'=>$idEmployee,
																					   'id company'=>$idCompnay,
																					   'details'=>$details]);
			
				$payLoad = ['status'=>'error','message'=>'Sorry!You have not permission to edit user.'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function updateUserOptions($params = []){

		$payLoad = [];
		if(!isset($params['id_user']) || 
		   (isset($params['id_user']) && !$this->app['helper']('Utility')->notEmpty($params['id_user'])) || 
		   !isset($params['owner_company']) || 
		   (isset($params['owner_company']) && !$this->app['helper']('Utility')->notEmpty($params['owner_company']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User id, Owner company'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			$idUser = $params['id_user'];
			$ownerCompany = $params['owner_company'];
			unset($params['id_user']);
			unset($params['owner_company']);
			$payLoad = $this->CompanyUser->editUser($idUser,$ownerCompany,$params);
			
		}
		
		return $payLoad;
		
	}
	public function userCompanyList($idUser){
		
		$fetch = $this->CompanyDetails->userCompany($idUser);
		return $fetch;
		
	}
	
	public function deleteCompany($request){
		
		$idUser = $request->get('id_user');
		$idCompany = $request->get('id_company');
		
		$findCompany = $this->SoftwareUser->getUserinfo($idUser,$idCompany);
		
		$payLoad = [];
		if($findCompany['maker_identify'] == $idUser){
			
			$payLoad = $this->CompanyDetails->deleteCompany($idCompany);
			if(isset($payLoad['status']) && $payLoad['status'] == 'success'){
				
				$companyLists = self::userCompanyList($idUser);
				$adminCompany = [];
				foreach($companyLists as $company){
					//if($company['user_type'] == 'SuperAdmin'){
						$adminCompany[] = $company;
					//}
				}
				if(count($adminCompany) < 1){
					$this->app['component']('oAuth_Models_SoftwareUser')->deleteUser($idUser);
				}
				
			}														
		}else{
			$payLoad = ['status'=>'error','message'=>'Sorry!You have not permission to delete this company.'];
		}
		
		return $payLoad;
		
	}
	
	public function userChild($request){
		
		$payLoad = [];
		$idUser = $request->get('id_user');
		$idCompany = $request->get('id_company');
		
		if(!$this->app['helper']('Utility')->notEmpty($idUser) || 
		  !$this->app['helper']('Utility')->notEmpty($idCompany)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id User,Id Company).'];
			
		}else{
			
			$tree = [];
			$allCompanyUser = $this->CompanyUser->allUserList($idCompany);
	
			foreach($allCompanyUser as $row){
			
				$nodeList[$row['identify']] = array_merge($row, array('children' => array()));

			}

			foreach ($nodeList as $nodeId => &$node) {
				
				
				if (!$node['maker_identify'] || !array_key_exists($node['maker_identify'], $nodeList) || ($nodeId == $node['maker_identify'])) {
					
					$tree[] = &$node;
				} else {
					
					$nodeList[$node['maker_identify']]['children'][] = &$node;
				}
			}
			unset($node);
			unset($nodeList);
			
			$resKeys = self::array_find_deep($tree,$idUser);
			$resKeysCount = count($resKeys);
			
			for($i = 0;$i < ($resKeysCount-1);++$i){
				$tree = $tree[$resKeys[$i]];
			}
			
			$children = self::getChild($tree['children']);
			$children[] = ['identify'=>$idUser,'name'=>'Myself'];
			$payLoad = $children;
		}

		return $payLoad;
	}
	
	private function array_find_deep($array, $search, $keys = array()){
		
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$sub = self::array_find_deep($value, $search, array_merge($keys, array($key)));
				if (count($sub)) {
					return $sub;
				}
			} elseif ($value === $search && $key == 'identify') {
				return array_merge($keys, array($key));
			}
		}

		return array();
		
	}
	
	private function getChild($source){
		
		$children = [];
		foreach($source as $row){
			
			if(isset($row['identify'])){
				
				if($this->app['helper']('Utility')->notEmpty($row['first_name']) || 
				  $this->app['helper']('Utility')->notEmpty($row['last_name'])){
					$name = $row['first_name'].' '.$row['last_name'];
				}else{
					$expoEmail = explode('@',$row['email']);
					//$name = $row['email'];
					$name = $expoEmail[0];
				}
				$children[] = ['identify'=>$row['identify'],
							   'name'=>$name];
				
			}
			
			if(isset($row['children'])){
				
				$children = array_merge($children, self::getChild($row['children']));
				
			}
			
		}
		return $children;
		
	}
	
	public function groupingUser($idCompany,$idUser){
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) || 
		   !$this->app['helper']('Utility')->notEmpty($idUser)){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User, Id Company'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			$allUsers = self::companyAllUserList($idCompany);
			$allUsersId = $this->app['helper']('ArrayFunc')->getSpeceficKey($allUsers,'identify','array');
			
			$userChild = $this->app['helper']('HandlleRequest')->returnResult('user/company','POST',['action'=>'child','id_user'=>$idUser,'id_company'=>$idCompany]);
			$children = $this->app['helper']('Utility')->convertResponseToArray($userChild);
			$childrenId = $this->app['helper']('ArrayFunc')->getSpeceficKey($children,'identify','array');
			
			$downUsersId = $childrenId;
			$upUsersId = array_diff($allUsersId,$childrenId);
			
			$payLoad['up'] = $upUsersId;
			$findMySelf = array_search($idUser,$downUsersId);
			$payLoad['mySelf'][] = $idUser;
			unset($downUsersId[$findMySelf]);
			$payLoad['down'] = $downUsersId;
			
			/*foreach($downUsersId as $id){
				
				$keys = array_keys(array_column($allUsers, 'identify'),$id);
				if($id == $idUser){
					$payLoad['my_self'][] = $allUsers[$keys[0]];
				}else{
					$payLoad['down'][] = $allUsers[$keys[0]];
				}
				
			}
			foreach($upUsersId as $id){
				$keys = array_keys(array_column($allUsers, 'identify'),$id);
				$payLoad['up'][] = $allUsers[$keys[0]];
			}*/
			
		}
		
		return $payLoad;
		
	} 

	
}