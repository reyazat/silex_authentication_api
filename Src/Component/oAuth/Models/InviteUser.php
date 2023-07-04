<?php
namespace Component\oAuth\Models;



class InviteUser extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'invite_user';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function makeFields($details = []){
		
		$fields = [];
		if(isset($details['unique_code']) && $this->app['helper']('Utility')->notEmpty($details['unique_code'])){
			$fields['unique_code'] = $details['unique_code'];
		}
		
		if(isset($details['id_company']) && $this->app['helper']('Utility')->notEmpty($details['id_company'])){
			$fields['id_company'] = $details['id_company'];
		}
		
		if(isset($details['id_maker']) && $this->app['helper']('Utility')->notEmpty($details['id_maker'])){
			$fields['id_maker'] = $details['id_maker'];
		}
		
		if(isset($details['id_role']) && $this->app['helper']('Utility')->notEmpty($details['id_role'])){
			$fields['id_role'] = $details['id_role'];
		}
		
		if(isset($details['email']) && $this->app['helper']('Utility')->notEmpty($details['email'])){
			$fields['email'] = $details['email'];
		}
		
		if(isset($details['mood']) && $this->app['helper']('Utility')->notEmpty($details['mood'])){
			$fields['mood'] = $details['mood'];
		}
		
		if(isset($details['user_type']) && $this->app['helper']('Utility')->notEmpty($details['user_type'])){
			$fields['user_type'] = $details['user_type'];
		}
		
		if(isset($details['tracking_id']) && $this->app['helper']('Utility')->notEmpty($details['tracking_id'])){
			$fields['tracking_id'] = $details['tracking_id'];
		}
		
		return $fields;
		
	}
	
	public function addInvite($details = []){

		$payLoad = [];
		$this->app['helper']('ModelLog')->Log();
		$details = self::makeFields($details);
		$details['cdate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
		
		$checkDuplicateInvite = self::checkDuplicateInvite($details['id_company'],$details['email']);
		
		if($this->app['helper']('Utility')->notEmpty($checkDuplicateInvite)){
			
			if($checkDuplicateInvite['mood'] == 'Canceld'){
				
				$changeMood = self::updateInvite($checkDuplicateInvite['id'],['mood'=>'Active','user_type'=>$details['user_type']]);
				if(isset($changeMood['status']) && $changeMood['status'] == 'error'){
					
					$payLoad = $changeMood;
					
				}else{
					
					$payLoad = ['status'=>'success','message'=>'invite added successfully.','code'=>$checkDuplicateInvite['unique_code'],'id'=>$checkDuplicateInvite['id']];
					
				}
				
				
			}else if($checkDuplicateInvite['mood'] == 'Active'){
				
				$payLoad = ['status'=>'error','message'=>'Invite with same email already exist in invite list.'];
				
			}else if($checkDuplicateInvite['mood'] == 'Used'){
				
				$payLoad = ['status'=>'error','message'=>'User with same email exist in your company user`s.'];
				
			}
			
		}else{
			
			$idInvite = InviteUser::insertGetId($details);
			if($this->app['helper']('Utility')->notEmpty($idInvite)){

				$payLoad = ['status'=>'success','message'=>'invite added successfully.','code'=>$details['unique_code'],'id'=>$idInvite];

			}else{

				$this->app['monolog.debug']->error('error in add new invite',$details);
				$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
				$payLoad = ['status'=>'error','message'=>$msg];

			}
			
		}
		
		
		
		return $payLoad;
		
	}
	
	public function updateInvite($idInvite,$details = []){
		$this->app['helper']('ModelLog')->Log();
		$payLoad = [];
		$details = self::makeFields($details);
		
		$updateInviteMode = InviteUser::where('id', $idInvite)->update($details);
		if($this->app['helper']('Utility')->notEmpty($updateInviteMode)){
			
			$payLoad = ['status'=>'success','message'=>'Invite status changed successfully.'];
			
		}else{
			
			$this->app['monolog.debug']->error('error occurred in update invite.',$details);
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred please contact support.'];
			
		}
		
		return $payLoad;
		
	}

	
	public function checkCode($code){
		$this->app['helper']('ModelLog')->Log();
		$checkCode = InviteUser::select('company_details.company_name',
										'invite_user.id',
										'invite_user.id_company',
										'invite_user.id_maker',
										'invite_user.email',
										'invite_user.user_type',
										'invite_user.mood')
								->leftjoin('company_details','company_details.id','=','invite_user.id_company')
								->where('invite_user.unique_code','=',$code)
								->get();
		
		if(isset($checkCode[0]) && $this->app['helper']('Utility')->notEmpty($checkCode[0])){
			
			$details = $checkCode[0]->toArray();
			$details['status'] = 'success';
			
			return $details;
			
		}else{
			
			return [];
			
		}
		
	}
	
	public function getInvite($idInvite){
		$this->app['helper']('ModelLog')->Log();
		$fetchInvite = InviteUser::where('id','=',$idInvite)->get();
		if(isset($fetchInvite[0]) && $this->app['helper']('Utility')->notEmpty($fetchInvite[0])){
			return $fetchInvite[0]->toArray();
		}else{
			return [];
		}
		
	}
	
	public function inviteList($idCompany,$idUser,$userType){
		
		$this->app['helper']('ModelLog')->Log();
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($idCompany) && 
		  $this->app['helper']('Utility')->notEmpty($idUser) && 
		  $this->app['helper']('Utility')->notEmpty($userType)){
			
			$fetchInvite = InviteUser::select('invite_user.id','invite_user.email','invite_user.mood','software_user.first_name','software_user.last_name','software_user.email as ownerMail')
									->leftjoin('software_user','software_user.identify','=','invite_user.id_maker')
									->where('invite_user.id_company','=',$idCompany);
			if($userType != 'SuperAdmin'){
				$fetchInvite = $fetchInvite->where('invite_user.id_maker','=',$idUser);
			}
			$fetchInvite = $fetchInvite->where('invite_user.mood','=','Active')
										->get();
		
			if(isset($fetchInvite[0]) && $this->app['helper']('Utility')->notEmpty($fetchInvite[0])){

				$payLoad = $fetchInvite->toArray();

			}else{

				$payLoad = [];

			}
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company,Id User,User Type).'];
			
		}
		
		return $payLoad;
		
	}
	
	public function removeFromInviteList($idCompany,$email){
		
		$this->app['helper']('ModelLog')->Log();
		$payLoad = [];
		$removeInvite = InviteUser::where('id_company', '=', $idCompany)
									->where('email','=',$email)
									->delete();
		
		if($this->app['helper']('Utility')->notEmpty($removeInvite)){
			$payLoad = ['status'=>'success','message'=>'Invite removed from list successfully.'];
		}else{
			
			$this->app['monolog.debug']->error('error in delete from invite list',['id_company'=>$idCompany,
																			  'email'=>$email]);
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurrd,please contact support.'];
		}
		
		return $payLoad;
		
	}

	public function getInviteByEmail($email,$idCompany){
		
		$findInvite = InviteUser::where('email','=',$email)
								->where('id_company','=',$idCompany)
								->get();
		
		if(isset($findInvite[0]) && $this->app['helper']('Utility')->notEmpty($findInvite[0])){
			return $findInvite[0]->toArray();
		}else{
			return [];
		}
		
	}
	
	public function checkDuplicateInvite($idCompany,$email){
		
		$this->app['helper']('ModelLog')->Log();
		$checkInvite = InviteUser::where('id_company','=',$idCompany)
								 ->where('email','=',$email)
								 ->get();
		
		if(isset($checkInvite[0]) && $this->app['helper']('Utility')->notEmpty($checkInvite[0])){
			
			return $checkInvite[0]->toArray();
			
		}else{
			
			return [];
			
		}
		
	}

	public function inviteCount($idCompany){
		
		$payLoad = [];
		if($this->app['helper']('Utility')->notEmpty($idCompany)){
			
			$countInvite = InviteUser::where('id_company','=',$idCompany)->where('mood','=','Active')->count();
			$payLoad = ['status'=>'success','count'=>$countInvite];
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Company).'];
			
		}
		
		return $payLoad;
		
	}
}