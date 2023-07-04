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
			$details['company_name'] = $res['company_name'];
		}
		
		if(isset($res['no_of_employee']) /*&& $this->app['helper']('Utility')->notEmpty($res['no_of_employee'])*/){
			$details['no_of_employee'] = $res['no_of_employee'];
		}/*else{
			$details['no_of_employee'] = NULL;
		}*/
		
		if(isset($res['register_no']) /*&& $this->app['helper']('Utility')->notEmpty($res['register_no'])*/){
			$details['register_no'] = $res['register_no'];
		}/*else{
			$details['register_no'] = NULL;
		}*/
		
		if(isset($res['industry']) /*&& $this->app['helper']('Utility')->notEmpty($res['industry'])*/){
			$details['industry'] = $res['industry'];
		}/*else{
			$details['industry'] = NULL;
		}*/
		
		if(isset($res['active_from']) /*&& $this->app['helper']('Utility')->notEmpty($res['active_from'])*/){
			$details['active_from'] = $res['active_from'];
		}
		
		if(isset($res['client_acquired']) /*&& $this->app['helper']('Utility')->notEmpty($res['client_acquired'])*/){
			$details['client_acquired'] = $res['client_acquired'];
		}
		
		if(isset($res['country']) /*&& $this->app['helper']('Utility')->notEmpty($res['country'])*/){
			$details['country'] = $res['country'];
		}/*else{
			$details['country'] = NULL;
		}*/
		
		if(isset($res['locality']) /*&& $this->app['helper']('Utility')->notEmpty($res['locality'])*/){
			$details['locality'] = $res['locality'];
		}/*else{
			$details['locality'] = NULL;
		}*/
		
		if(isset($res['address1']) /*&& $this->app['helper']('Utility')->notEmpty($res['address1'])*/){
			$details['address1'] = $res['address1'];
		}/*else{
			$details['address1'] = NULL;
		}*/
		
		if(isset($res['address2']) /*&& $this->app['helper']('Utility')->notEmpty($res['address2'])*/){
			$details['address2'] = $res['address2'];
		}/*else{
			$details['address2'] = NULL;
		}*/
		
		if(isset($res['postcode']) /*&& $this->app['helper']('Utility')->notEmpty($res['postcode'])*/){
			$details['postcode'] = $res['postcode'];
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
		
		if(isset($res['id_packs']) /*&& $this->app['helper']('Utility')->notEmpty($res['id_packs'])*/){
			$details['id_packs'] = $res['id_packs'];
		}
		
		if(isset($res['timezone']) /*&& $this->app['helper']('Utility')->notEmpty($res['timezone'])*/){
			$details['timezone'] = $res['timezone'];
		}
		
		if(isset($res['company_status']) /*&& $this->app['helper']('Utility')->notEmpty($res['company_status'])*/){
			$details['company_status'] = $res['company_status'];
		}/*else{
			$details['company_status'] = NULL;
		}*/
		
		if(isset($res['first_setup']) /*&& $this->app['helper']('Utility')->notEmpty($res['first_setup'])*/){
			$details['first_setup'] = $res['first_setup'];
		}
		
		return $details;
	}
	
	public function addCompany($res){
		$this->app['helper']('ModelLog')->Log();
		$details = self::makeCompanyFields($res);
		
		$DateTimeFunc = new DateTimeFunc($this->app);
		$details['cdate'] = $DateTimeFunc->nowDateTime();
		if(!isset($details['company_name']) || !$this->app['helper']('Utility')->notEmpty($details['company_name'])){
			
			return ['status'=>'error','message'=>'Some required field are empty(Company Name)'];
			
		}else{
			
			$utility = new Utility();
			$details['rand_email'] = $utility->slug($details['company_name']);
			
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
												   'company_details.locality',
												   'company_details.address1',
												   'company_details.address2',
												   'company_details.postcode',
												   'company_details.cdate')
											->leftjoin('company_user','company_user.id_company','=','company_details.id')
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
}