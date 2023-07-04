<?php
namespace Component\oAuth\Models;
use Helper\DateTimeFunc;
use Helper\Utility;

class CompanyDetails extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'company_details';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeCompanyFields($res){
		
		$details = [];
		if(isset($res['company_name']) /*&& $this->app['helper']('Utility')->notEmpty($res['company_name'])*/){
			$details['company_name'] = $this->app['helper']('Utility')->clearField($res['company_name']);
		}
		
		if(isset($res['api_key']) /*&& $this->app['helper']('Utility')->notEmpty($res['company_name'])*/){
			$details['api_key'] = $res['api_key'];
		}
		
		if(isset($res['integrate_with_accounting']) /*&& $this->app['helper']('Utility')->notEmpty($res['company_name'])*/){
			$details['integrate_with_accounting'] = $res['integrate_with_accounting'];
		}
		
		if(array_key_exists('integrate_details',$res) /*&& $this->app['helper']('Utility')->notEmpty($res['company_name'])*/){
			$details['integrate_details'] = json_encode($res['integrate_details']);
		}
		
		if(array_key_exists('fm_perms',$res) /*&& $this->app['helper']('Utility')->notEmpty($res['company_name'])*/){
			$details['fm_perms'] = json_encode($res['fm_perms']);
		}
		if(isset($res['no_of_employee']) /*&& $this->app['helper']('Utility')->notEmpty($res['no_of_employee'])*/){
			$details['no_of_employee'] = $this->app['helper']('Utility')->clearField($res['no_of_employee']);
		}/*else{
			$details['no_of_employee'] = NULL;
		}*/
		
		if(isset($res['register_no']) /*&& $this->app['helper']('Utility')->notEmpty($res['register_no'])*/){
			$details['register_no'] = $res['register_no'];
		}/*else{
			$details['register_no'] = NULL;
		}*/
		
		if(isset($res['industry']) /*&& $this->app['helper']('Utility')->notEmpty($res['industry'])*/){
			$details['industry'] = $this->app['helper']('Utility')->clearField($res['industry']);
		}/*else{
			$details['industry'] = NULL;
		}*/
		
		if(isset($res['active_from']) /*&& $this->app['helper']('Utility')->notEmpty($res['active_from'])*/){
			$details['active_from'] = $this->app['helper']('Utility')->clearField($res['active_from']);
		}
		
		if(isset($res['client_acquired']) /*&& $this->app['helper']('Utility')->notEmpty($res['client_acquired'])*/){
			$details['client_acquired'] = $res['client_acquired'];
		}
		
		if(isset($res['country']) /*&& $this->app['helper']('Utility')->notEmpty($res['country'])*/){
			$details['country'] = $this->app['helper']('Utility')->clearField($res['country']);
		}/*else{
			$details['country'] = NULL;
		}*/
		
		if(isset($res['locality']) /*&& $this->app['helper']('Utility')->notEmpty($res['locality'])*/){
			$details['locality'] = $this->app['helper']('Utility')->clearField($res['locality']);
		}/*else{
			$details['locality'] = NULL;
		}*/
		
		if(isset($res['address1']) /*&& $this->app['helper']('Utility')->notEmpty($res['address1'])*/){
			$details['address1'] = $this->app['helper']('Utility')->clearField($res['address1']);
		}/*else{
			$details['address1'] = NULL;
		}*/
		
		if(isset($res['address2']) /*&& $this->app['helper']('Utility')->notEmpty($res['address2'])*/){
			$details['address2'] = $this->app['helper']('Utility')->clearField($res['address2']);
		}/*else{
			$details['address2'] = NULL;
		}*/
		
		if(isset($res['postcode']) /*&& $this->app['helper']('Utility')->notEmpty($res['postcode'])*/){
			$details['postcode'] = $this->app['helper']('Utility')->clearField($res['postcode']);
		}/*else{
			$details['postcode'] = NULL;
		}*/
		
		if(isset($res['sic_codes']) /*&& $this->app['helper']('Utility')->notEmpty($res['sic_codes'])*/){
			$details['sic_codes'] = $res['sic_codes'];
		}/*else{
			$details['sic_codes'] = NULL;
		}*/
		
		if(isset($res['can_file']) /*&& $this->app['helper']('Utility')->notEmpty($res['can_file'])*/){
			$details['can_file'] = $res['can_file'];
		}
		
		if(isset($res['date_of_cessation']) /*&& $this->app['helper']('Utility')->notEmpty($res['date_of_cessation'])*/){
			$details['date_of_cessation'] = $res['date_of_cessation'];
		}
		
			
		if(isset($res['timezone']) /*&& $this->app['helper']('Utility')->notEmpty($res['timezone'])*/){
			$details['timezone'] = $this->app['helper']('Utility')->secureInput($res['timezone']);
		}
		
		if(isset($res['id_plans']) /*&& $this->app['helper']('Utility')->notEmpty($res['id_plans'])*/){
			$details['id_plans'] = $res['id_plans'];
		}
		if(isset($res['users_count']) /*&& $this->app['helper']('Utility')->notEmpty($res['users_count'])*/){
			$details['users_count'] = $res['users_count'];
		}
		if(isset($res['payment_status']) /*&& $this->app['helper']('Utility')->notEmpty($res['payment_status'])*/){
			$details['payment_status'] = $res['payment_status'];
		}
		if(isset($res['due_date']) /*&& $this->app['helper']('Utility')->notEmpty($res['due_date'])*/){
			$details['due_date'] = $res['due_date'];
		}
		
		if(isset($res['company_status']) /*&& $this->app['helper']('Utility')->notEmpty($res['company_status'])*/){
			$details['company_status'] = $res['company_status'];
		}/*else{
			$details['company_status'] = NULL;
		}*/
		
		if(isset($res['first_setup']) /*&& $this->app['helper']('Utility')->notEmpty($res['first_setup'])*/){
			$details['first_setup'] = $res['first_setup'];
		}
		
		if(isset($res['pre_defined_reason']) /*&& $this->app['helper']('Utility')->notEmpty($res['first_setup'])*/){
			$details['pre_defined_reason'] = $res['pre_defined_reason'];
		}
		
		if(isset($res['last_login']) /*&& $this->app['helper']('Utility')->notEmpty($res['first_setup'])*/){
			$details['last_login'] = $res['last_login'];
		}
		
		return $details;
		
	}
	
	public function addCompany($res){
		$this->app['helper']('ModelLog')->Log();
		$details = self::makeCompanyFields($res);
		
		$DateTimeFunc = new DateTimeFunc($this->app);
		$details['cdate'] = $DateTimeFunc->nowDateTime();

		if(!isset($details['company_name']) || !$this->app['helper']('Utility')->notEmpty($details['company_name']) || !isset($details['payment_status'])){
			
			return ['status'=>'error','message'=>'Some required field is empty: Company Name'];
			
		}else if(!isset($details['payment_status'])){
			
			return ['status'=>'error','message'=>'Some required field is empty: Selected Plan'];
			
		}else{
			
			$utility = new Utility();
			$details['rand_email'] = $utility->slug($details['company_name']);
			
			$getUuid = $this->app['helper']('CryptoGraphy')->createUUID();
			$details['api_key'] = str_replace('-','',$getUuid['uuid']);
			
			$id = CompanyDetails::insertGetId($details);
		
			if($this->app['helper']('Utility')->notEmpty($id)){

				// call to create crm tables
				/*$this->app['helper']('OutgoingRequest')->postRequest($this->app['config']['webservice']['crm'].'system/table',[],['id'=>$id]);*/
				return $id;
				
			}else{

				$this->app['monolog.debug']->debug('error occurred in add new company',$details);
				return '';
				
			}
			
		}

	}
	
	public function companyInfo($idCompany){
		
		$this->app['helper']('ModelLog')->Log();
		$findCompany = CompanyDetails::where('id','=',$idCompany)->get();
		
		if(isset($findCompany[0]) && $this->app['helper']('Utility')->notEmpty($findCompany[0])){
			
			$findCompany[0]->company_name = $this->app['helper']('Utility')->mysql_escape_mimic_inverse($findCompany[0]->company_name);
			
			$nowDataTime = $this->app['helper']('DateTimeFunc')->nowDateTime();
			self::editCompany($idCompany,['last_login'=>$nowDataTime]);
			
			return $findCompany[0]->toArray();
			
		}else{
			
			return [];
			
		}
		
	}
	
	public function companyInfoByApiKey($apiKey){
		
		$this->app['helper']('ModelLog')->Log();
		$findCompany = CompanyDetails::where('api_key','=',$apiKey)->get();
		
		if(isset($findCompany[0]) && $this->app['helper']('Utility')->notEmpty($findCompany[0])){
			
			return $findCompany[0]->toArray();
			
		}else{
			
			return [];
			
		}
		
	}
	
	public function userCompany($idUser){
		$this->app['helper']('ModelLog')->Log();
		$fetchUseCompany = CompanyDetails::select('company_details.id',
												   'company_details.company_name',
												   'company_details.register_no',
												   'company_details.no_of_employee',
												   'company_details.industry',
												   'company_details.active_from',
												   'company_details.country',
												   'company_details.users_count',
												   'plans.name',
												   'company_details.payment_status',
												   'company_details.due_date',
												   'company_details.locality',
												   'company_details.address1',
												   'company_details.address2',
												   'company_details.postcode',
												   'company_details.cdate')
											->leftjoin('company_user','company_user.id_company','=','company_details.id')
											->leftjoin('plans','plans.identify','=','company_details.id_plans')
											->where('company_user.maker_identify','=',$idUser)
											->groupby('company_details.id')
											->get();
		
		return $fetchUseCompany->toArray();
		
	}
	
	public function editCompany($idCompany,$details){
		
		$this->app['helper']('ModelLog')->Log();
		$updateFields = self::makeCompanyFields($details);
	
		$editDetails = CompanyDetails::where('id', $idCompany)
            							->update($updateFields);
		
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($editDetails)){
			
			$payLoad = ['status'=>'success','message'=>'Company edited successfully.'];
			
		}else{
			
			$this->app['monolog.debug']->error('error in edit company details',['id'=>$idCompany,'details'=>$details]);
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please conact support.'];
		}
		
		return $payLoad;
		
	}
	
	public function deleteCompany($idCompany){
		$this->app['helper']('ModelLog')->Log();
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($idCompany)){
			
			$deleteCompany = CompanyDetails::where('id','=',$idCompany)->delete();
			if($this->app['helper']('Utility')->notEmpty($deleteCompany)){

				$payLoad = ['status'=>'success','message'=>'Company deleted successfully.'];
				
			}else{
				
				$this->app['monolog.debug']->error('Error in delete compay',['id company'=>$idCompany]);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}
			
		}else{
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company).'];
		}
		
		return $payLoad;
		
	}
	
	public function existOneCompany($idUser,$idCompany){
		$this->app['helper']('ModelLog')->Log();
		$companyList = CompanyDetails::select('company_user.id_company as id',
										   'company_details.id_plans',
										   'company_details.company_name')
									->leftJoin('company_user', 'company_user.id_company', '=', 'company_details.id')
									->where('company_user.user_identify','=',$idUser)
									->where('company_details.id','=',$idCompany)
									->where('company_user.status','=','Active')
									->first();
		
		
		return $companyList->toArray();
		
	}
	
	public function allCompany(){
		
		$allCompany = CompanyDetails::get()->toArray();
		return $allCompany;
		
	}

	public function freeCheckoutCompany()
	{
		
		$now = $this->app['helper']('DateTimeFunc')->nowDate();

		return CompanyDetails::select(['plans.identify','plans.service_id','plans.service_name','plans.name',
			'company_details.id','company_details.company_name','company_details.register_no',
			'company_details.country','company_details.locality','company_details.address1',
			'company_details.postcode','company_details.users_count','company_user.user_identify',
			'software_user.first_name','software_user.last_name','software_user.email',
			'company_details.address1','company_details.address2','company_details.country',
			'company_details.locality','company_details.postcode',
		])
				->join('plans','plans.identify','=','company_details.id_plans')
				->join('company_user','company_user.id_company','=','company_details.id')
				->join('software_user','company_user.user_identify','=','software_user.identify')
				->where('plans.name','=','Free')
				->where('company_user.user_type','=','SuperAdmin')
				->whereDate('company_details.due_date','=',$now)
				->get()->toArray();	

	}
	
}