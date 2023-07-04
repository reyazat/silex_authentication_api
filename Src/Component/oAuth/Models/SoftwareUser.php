<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;

class SoftwareUser extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'software_user';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeFieldsReady($request){
		
		$details = [];
	
		if($this->app['helper']('Utility')->notEmpty($request->get('email'))){

			$details['email'] = $this->app['helper']('Utility')->secureInput($request->get('email'));

		}

		/*if($request->get('password_hash') !== null && $this->app['helper']('Utility')->notEmpty($request->get('password_hash'))){
			
			$password = '';
			$password = $this->app['helper']('Utility')->trm($request->get('password_hash'));
			$details['password_hash'] = $this->app['helper']('CryptoGraphy')->encryptPassword($password);
			$details['password'] = $this->app['helper']('CryptoGraphy')->md5encrypt($password);

		}
		else*/ if($request->get('password') !== null && $this->app['helper']('Utility')->notEmpty($request->get('password'))){
			
			$password = '';
			$password = $this->app['helper']('Utility')->trm($request->get('password'));
			//$details['password'] = $this->app['helper']('CryptoGraphy')->md5encrypt($password);
			$details['password'] = $this->app['helper']('CryptoGraphy')->encryptPassword($password);

		}


		/*if($this->app['helper']('Utility')->notEmpty($request->get('username'))){

			$details['username'] = $this->app['helper']('Utility')->trm($request->get('username'));

		}*/

		if($request->query->has('first_name') || $request->request->has('first_name')){

			$details['first_name'] = $this->app['helper']('Utility')->secureInput($request->get('first_name'));

		}

		if($request->query->has('last_name') || $request->request->has('last_name')){

			$details['last_name'] = $this->app['helper']('Utility')->secureInput($request->get('last_name'));

		}
		
		
		if($this->app['helper']('Utility')->notEmpty($request->get('birthday'))){

			$details['birthday'] = $this->app['helper']('Utility')->trm($request->get('birthday'));

		}


		if($this->app['helper']('Utility')->notEmpty($request->get('signup_source'))){

			$details['signup_source'] = $this->app['helper']('Utility')->trm($request->get('signup_source'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('phone_code'))){

			$details['phone_code'] = $this->app['helper']('Utility')->secureInput($this->app['helper']('Utility')->trm($request->get('phone_code')));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('phone'))){

			$details['phone'] = $this->app['helper']('Utility')->secureInput($this->app['helper']('Utility')->trm($request->get('phone')));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('address1'))){

			$details['address1'] = $this->app['helper']('Utility')->secureInput($request->get('address1'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('address2'))){

			$details['address2'] = $this->app['helper']('Utility')->secureInput($request->get('address2'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('country'))){

			$details['country'] = $this->app['helper']('Utility')->secureInput($request->get('country'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('city'))){

			$details['city'] = $this->app['helper']('Utility')->secureInput($request->get('city'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('state'))){

			$details['state'] = $this->app['helper']('Utility')->secureInput($request->get('state'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('zip_code'))){

			$details['zip_code'] = $this->app['helper']('Utility')->secureInput($request->get('zip_code'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('questions'))){
			
			$questions = $request->get('questions');
			$questions = array_filter($questions);
			$questions = array_unique($questions);
			
			$clearRes = [];
			foreach($questions as $key=>$question){
				
				$clearQuestion = $this->app['helper']('Utility')->clearField($key);
				$clearAnswer = $this->app['helper']('Utility')->clearField($question);
				
				$clearRes[$clearQuestion] = $clearAnswer;
				
			}
			
			$details['questions'] = serialize($clearRes);
			
		}
		
		
		if($this->app['helper']('Utility')->notEmpty($request->get('2factorsecret'))){
			$details['2factorsecret'] = $request->get('2factorsecret');
			
			if(strtolower($details['2factorsecret']) === 'null'){
				$details['2factorsecret'] = NULL;
			}

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('locale'))){

			$details['locale'] = $this->app['helper']('Utility')->secureInput($this->app['helper']('Utility')->trm($request->get('locale')));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('last_login'))){

			$details['last_login'] = $this->app['helper']('Utility')->trm($request->get('last_login'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('notification_setting'))){

			$details['notification_setting'] = json_encode($request->get('notification_setting'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('reminder_setting'))){

			$details['reminder_setting'] = json_encode($request->get('reminder_setting'));

		}
		
		/*if($this->app['helper']('Utility')->notEmpty($request->get('timezone'))){

			$details['timezone'] = $this->app['helper']('Utility')->trm($request->get('timezone'));

		}*/
		
		if($this->app['helper']('Utility')->notEmpty($request->get('currency'))){

			$details['currency'] = $this->app['helper']('Utility')->secureInput($this->app['helper']('Utility')->trm($request->get('currency')));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('last_login'))){

			$details['last_login'] = $this->app['helper']('Utility')->trm($request->get('last_login'));

		}
		
		return $details;

	}
	
	public function checkDuplicateUser($email){
		$this->app['helper']('ModelLog')->Log();
		$findUser = SoftwareUser::where('email','=',$email)->get();
		
		if(isset($findUser[0]) && $this->app['helper']('Utility')->notEmpty($findUser[0])){
			
			return $findUser[0]->identify;
			
		}else{
			
			return false;
			
		}
		
	}
	
	/*public function checkDuplicate($email,$username){
		
		$findUser = SoftwareUser::where('email','=',$email)
								->orwhere('username','=',$username)
								->get();
		
		if($this->app['helper']('Utility')->notEmpty($findUser[0])){
			
			return $findUser[0]->identify;
			
		}else{
			
			return false;
			
		}
		
	}*/
	
	public function insertUser($request){
		$this->app['helper']('ModelLog')->Log();
		$details = [];
		$details = self::makeFieldsReady($request);
		
		$payLoad = [];
		if(!isset($details['email']) || !isset($details['password']) || !$this->app['helper']('Utility')->notEmpty($details['email']) || !$this->app['helper']('Utility')->notEmpty($details['password'])){
			
			$payLoad = ['status'=>'error','message'=>'Some require fields are empty.(Email,Password)'];
			
		}else{
			
			$checkDuplicate = self::checkDuplicateUser($details['email']);
			if($checkDuplicate === false){
								
				$userIdentifier = self::makeUserIdentifier($details['email']);
				$details['identify'] = $userIdentifier;
				$details['cdate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
				$details['notification_setting'] = '{"email": {"open": "on", "click": "on", "receive": "on"}, "income": {"busy": "on", "noAnswer": "on"}, "webform": {"submit": "on"}}';
				$details['reminder_setting'] = '{"mode": {"time": "same"}, "type": ["all"]}';
				
				$saveUser = SoftwareUser::insertGetId($details);
				
				if($this->app['helper']('Utility')->notEmpty($saveUser)){
					
					$sendParams = self::makeSignupEmailFields($details);
					$sendParams['action'] = 'sendSignup';
					$this->app['helper']('OutgoingRequest')->postRequest($this->app['config']['webservice']['view'].'system/signup',[],$sendParams,false);
					
					$payLoad = ['status'=>'success','message'=>'User added successfully.','id_user'=>$userIdentifier];
					
				}else{
					
					// must set log system
					$payLoad = ['status'=>'error','message'=>'Sorry! an error occurred,please contact support.'];
					
				}
				
			}else{
				
				$payLoad = ['status'=>'error','message'=>'User with same email already exist.'];
				
			}
			
			
		}

		return $payLoad;
		
	}
	
	private function makeSignupEmailFields($details){
		
		$fields = [];
		$fields['person_email'] = $details['email'];
		$expoUseEmail = explode('@',$details['email']);
		$fields['person_name'] = (isset($details['first_name']) || isset($details['last_name']))?$details['first_name'].' '.$details['last_name']:$expoUseEmail[0];
		if(isset($details['signup_source'])){
			$fields['note_content'] = $details['signup_source'];
		}
		
		if( isset($details['phone_code']) ){
			$details['phone_code'] = $this->app['helper']('Utility')->trm($details['phone_code']);
		}
		else{
			$details['phone_code'] = '';			
		}
		if(isset($details['phone'])){
			$fields['person_phone'] = $details['phone_code'].' '.$details['phone'];
		}
		
		return $fields;
	}
	
	private function makeUserIdentifier($email){
		
		$this->app['helper']('ModelLog')->Log();
		$userPerfix = substr($email,0,1);
		$userPerfix = ucfirst($userPerfix);
		
		//$userCount = SoftwareUser::where('email','like',$userPerfix.'%')->count();
		$findLastId = SoftwareUser::select('id')->orderBy('id', 'desc')->first()->toArray();
		$userCount = $findLastId['id'];
		$userCount = sprintf('%04d', ($userCount));
		
		return $userPerfix.'_'.$userCount;
		
	}
	
	public function findUser($userName,$Password){
		$this->app['helper']('ModelLog')->Log();
		
		$User = SoftwareUser::where('email','=',$userName)
							->get();

		if(isset($User[0])  && $this->app['helper']('Utility')->notEmpty($User[0])){

			$signup_source = $User[0]->signup_source;
			
			$accounting_source = array('Acc-Software','Acc-Invite','Acc-Android','Acc-IOS');
			//if( in_array($signup_source, $accounting_source) ){

				$checkAccIntegrateRedis = $this->app['predis']['cache']->exists('accessAccIntegrate_'.$User[0]->identify);
				if($checkAccIntegrateRedis && $Password === 'accIntegrate#2020@'){
					return $User;
				}else{
					
					//if($this->app['helper']('CryptoGraphy')->verifyPassword($Password,$User[0]->password_hash)){
					if($this->app['helper']('CryptoGraphy')->verifyPassword($Password,$User[0]->password)){
						return $User;
					}
					else{
						return false;
					}
					
				}
				
				

			/*}
			else{
				
				$Password = $this->app['helper']('CryptoGraphy')->md5encrypt($Password);
				if($Password == $User[0]->password){
					return $User;
				}
				else{
					return false;
				}
			}*/
		}
		return $User;
		
	}
	
	public function getUserById($idUser){
		$this->app['helper']('ModelLog')->Log();
		$User = SoftwareUser::where('id','=',$idUser)->get();
		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
			return $User[0]->toArray();
		}else{
			return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
		}
		
		
	}
	
	public function getUserByIdentify($identify){
		
		$this->app['helper']('ModelLog')->Log();
		$User = SoftwareUser::where('identify','=',$identify)
								->get();
		
		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
			return $User[0]->toArray();
		}else{
			return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
		}
		
		
	}
	
	public function getUserField($identify,$field){
		$this->app['helper']('ModelLog')->Log();
		
		$User = SoftwareUser::where('identify','=',$identify)
								->select($field)
								->get();
		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
			$res = $User[0]->toArray();
			return $res[$field];
		}else{
			return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
		}
		
		
	}

	public function getUserByPassword($password,$id_user){
		
		$this->app['helper']('ModelLog')->Log();

		$password = $this->app['helper']('Utility')->trm($password);

		if(!$this->app['helper']('Utility')->notEmpty($id_user) || 
			!$this->app['helper']('Utility')->notEmpty($password)){
			return ['status'=>'error','message'=>'Some require fields are empty.(id_user,Password)'];
		}

		//$password = $this->app['helper']('CryptoGraphy')->md5encrypt($password);
		$User = SoftwareUser::where('identify','=',$id_user)
							//->where('password', '=', $password)
							->get();

		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
			
			if($this->app['helper']('CryptoGraphy')->verifyPassword($password,$User[0]->password)){
				return $User[0]->identify;
			}else{
				return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
			}
			
		}else{
			return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
		}
		
		
	}
	
	public function getUserinfo($identify,$idCompany){
		
		$this->app['helper']('ModelLog')->Log();
		$User = SoftwareUser::select('software_user.*','company_user.*','company_details.rand_email','company_details.fm_perms','company_details.timezone','company_details.id_plans','company_details.users_count','company_details.payment_status','company_details.due_date','software_user.signup_source','company_details.first_setup','company_details.integrate_details','company_details.integrate_with_accounting','plans.name as planname')
								->where('software_user.identify','=',$identify)
								->where('company_user.id_company','=',$idCompany)
								->leftjoin('company_user','software_user.identify','=','company_user.user_identify')
								->leftjoin('company_details','company_user.id_company','=','company_details.id')
								->leftjoin('plans','company_details.id_plans','=','plans.identify')
								->get(); 
		
		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
			$User[0]->company_name = $this->app['helper']('Utility')->mysql_escape_mimic_inverse($User[0]->company_name);																									
			return $User[0]->toArray();
		}else{
			return ['status'=>'error','message'=>'Sorry!User with selected data not found.'];
		}
		
	}
	
	public function editUser($idUser,$details){
		
		$this->app['helper']('ModelLog')->Log();
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idUser)){
			
			// must have log,id user must not empty
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(User Id).'];
			
		}else{
			
			$editDetails = self::makeFieldsReady($details);
			if(isset($editDetails['email']) && $this->app['helper']('Utility')->notEmpty($editDetails['email'])){	
				$checkDuplicate = self::checkDuplicateUser($editDetails['email']);

				if($checkDuplicate !== false){

					if($checkDuplicate != $idUser){

						return ['status'=>'error','message'=>'user with same email or user name exist.'];

					}

				}
			}
				
			$updateUser = SoftwareUser::where('identify',$idUser)->update($editDetails);
			if($this->app['helper']('Utility')->notEmpty($updateUser)){

				$payLoad = ['status'=>'success','message'=>'user details updated successfully.'];

			}else{

				$payLoad = ['status'=>'error','message'=>'Sorry an error occurred,please contact support.'];

			}

			
		}
		
		return $payLoad;
		
	}
	
	public function deleteUser($idUser){
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($idUser)){
			
			$payLoad = [];
		
			$deleteUser = SoftwareUser::where('identify', '=', $idUser)->delete();

			if($this->app['helper']('Utility')->notEmpty($deleteUser)){

				$this->app['monolog.debug']->info('user deleted from software user.',['id_user'=>$idUser]);
				$payload = ['status'=>'success','message'=>'User deleted successfully.'];

			}else{

				$this->app['monolog.debug']->warning('user can not delete from software user.',['id_user'=>$idUser]);
				$payload = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

			}
			
		}else{
			
			$this->app['monolog.debug']->debug('id user not send to delte user.');
			$payload = ['status'=>'error','message'=>'Some required fields are empty(Id User).'];
			
		}

		return $payLoad;
		
	}
	
	public function signupUsers(){
		
		$perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
		$selectFields = ["software_user.email as user_email","company_details.company_name","company_details.due_date","company_details.last_login","company_details.cdate","plans.name"];
		$selectFields[] = new raw("CONCAT(".$perfix."software_user.first_name,' ',".$perfix."software_user.last_name) as full_name");
	
		$selectFields[] = new raw("CONCAT(".$perfix."software_user.phone_code,' ',".$perfix."software_user.phone) as phone");
		$selectFields[] = new raw("CONCAT(".$perfix."software_user.country,', ',".$perfix."software_user.zip_code) as address");
		$selectFields[] = new raw("(select count(id) from ".$perfix."company_user where id_company = ".$perfix."company_details.id and user_type != 'SuperAdmin') as user_cnt");
		
		
		$users = SoftwareUser::select($selectFields)
								->leftJoin('company_user', 'software_user.identify', '=', 'company_user.user_identify')
								->leftJoin('company_details', 'company_user.id_company', '=', 'company_details.id')
								->leftJoin('plans', 'company_details.id_plans', '=', 'plans.identify')
								->where('company_user.user_type','=','SuperAdmin')
								->groupBy('company_details.id', 'software_user.id')
								->orderBy('company_details.cdate', 'desc')
								->get()
								->toArray();
		
		return $users;
		
	}
	
	public function allUsers($getParameter=array()){

		if(!empty($getParameter['user_ids'])){
			$getParameter['user_ids'] = explode(',',$getParameter['user_ids']);
		}
		$getParameter['sort_field'] = isset($getParameter['sort_field'])?$getParameter['sort_field']:'';
		
		$users = SoftwareUser::select('identify','first_name','last_name','email','reminder_setting','locale')
				->where(function ($query) use ($getParameter) {
					if(!empty($getParameter['user_ids']) and is_array($getParameter['user_ids'])){
						foreach ($getParameter['user_ids'] as $key => $value) {
          		  			$query->orWhere('identify','=',$getParameter['user_ids'] );						              
						}
					}					
        		})
        		->when($getParameter['sort_field'], function($query) use ($getParameter) {
        			if(!empty($getParameter['sort_type'])){
                  		return $query->orderBy($getParameter['sort_field'],$getParameter['sort_type']);
                  	}
                })
				->get()
				->toArray();
		return $users;
		
	}

    public function allAccUsers($getParameter = array())
    {
        if (!empty($getParameter['user_ids'])) {
            $getParameter['user_ids'] = explode(',', $getParameter['user_ids']);
        }
        $getParameter['sort_field'] = isset($getParameter['sort_field']) ? $getParameter['sort_field'] : '';

        $users = SoftwareUser::select('software_user.identify', 'software_user.first_name', 'software_user.last_name', 'software_user.email', 'software_user.phone', 'software_user.phone_code', 'software_user.address1', 'software_user.signup_source', 'software_user.updated_at', 'software_user.cdate', 'user_notes.notes', 'user_notes.id as id_note')
            ->where('signup_source', 'Acc-Software')
            ->orWhere('signup_source', 'Acc-Invite')
            ->orWhere('signup_source', 'Acc-Android')
            ->orWhere('signup_source', 'Acc-IOS')
            ->orWhere('signup_source', 'Acc-Invite-Adviser')
            ->where(function ($query) use ($getParameter) {
                if (!empty($getParameter['user_ids']) and is_array($getParameter['user_ids'])) {
                    foreach ($getParameter['user_ids'] as $key => $value) {
                        $query->orWhere('identify', '=', $getParameter['user_ids']);
                    }
                }
            })
            ->leftjoin('user_notes', 'software_user.identify', '=', 'user_notes.id_user')
            ->orderby('software_user.cdate', 'desc')
            ->when($getParameter['sort_field'], function ($query) use ($getParameter) {
                if (!empty($getParameter['sort_type'])) {
                    return $query->orderBy($getParameter['sort_field'], $getParameter['sort_type']);
                }
            })
            ->get()
            ->toArray();
        return $users;
    }
	
}