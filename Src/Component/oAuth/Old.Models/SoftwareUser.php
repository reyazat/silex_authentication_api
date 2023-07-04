<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;

class SoftwareUser extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'software_user';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	private function makeFieldsReady($request){
		
		$details = [];
	
		if($this->app['helper']('Utility')->notEmpty($request->get('email'))){

			$details['email'] = $this->app['helper']('Utility')->trm($request->get('email'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('password'))){
			
			$password = '';
			$password = $this->app['helper']('Utility')->trm($request->get('password'));
			$details['password'] = $this->app['helper']('CryptoGraphy')->md5encrypt($password);

		}

		/*if($this->app['helper']('Utility')->notEmpty($request->get('username'))){

			$details['username'] = $this->app['helper']('Utility')->trm($request->get('username'));

		}*/

		if($this->app['helper']('Utility')->notEmpty($request->get('first_name'))){

			$details['first_name'] = $this->app['helper']('Utility')->trm($request->get('first_name'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('last_name'))){

			$details['last_name'] = $this->app['helper']('Utility')->trm($request->get('last_name'));

		}
		
		
		if($this->app['helper']('Utility')->notEmpty($request->get('birthday'))){

			$details['birthday'] = $this->app['helper']('Utility')->trm($request->get('birthday'));

		}


		if($this->app['helper']('Utility')->notEmpty($request->get('signup_source'))){

			$details['signup_source'] = $this->app['helper']('Utility')->trm($request->get('signup_source'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('phone_code'))){

			$details['phone_code'] = $this->app['helper']('Utility')->trm($request->get('phone_code'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('phone'))){

			$details['phone'] = $this->app['helper']('Utility')->trm($request->get('phone'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('address1'))){

			$details['address1'] = $this->app['helper']('Utility')->trm($request->get('address1'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('address2'))){

			$details['address2'] = $this->app['helper']('Utility')->trm($request->get('address2'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('country'))){

			$details['country'] = $this->app['helper']('Utility')->trm($request->get('country'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('city'))){

			$details['city'] = $this->app['helper']('Utility')->trm($request->get('city'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('state'))){

			$details['state'] = $this->app['helper']('Utility')->trm($request->get('state'));

		}

		if($this->app['helper']('Utility')->notEmpty($request->get('zip_code'))){

			$details['zip_code'] = $this->app['helper']('Utility')->trm($request->get('zip_code'));

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('questions'))){
			
				$details['questions'] = serialize($request->get('questions'));
		}
		
		
		if($this->app['helper']('Utility')->notEmpty($request->get('2factorsecret'))){
			$details['2factorsecret'] = $request->get('2factorsecret');
			
			if(strtolower($details['2factorsecret']) === 'null'){
				$details['2factorsecret'] = NULL;
			}

		}
		
		if($this->app['helper']('Utility')->notEmpty($request->get('locale'))){

			$details['locale'] = $this->app['helper']('Utility')->trm($request->get('locale'));

		}
		
		/*if($this->app['helper']('Utility')->notEmpty($request->get('timezone'))){

			$details['timezone'] = $this->app['helper']('Utility')->trm($request->get('timezone'));

		}*/
		
		if($this->app['helper']('Utility')->notEmpty($request->get('currency'))){

			$details['currency'] = $this->app['helper']('Utility')->trm($request->get('currency'));

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
				
				$saveUser = SoftwareUser::insertGetId($details);
				
				if($this->app['helper']('Utility')->notEmpty($saveUser)){
					
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
		$Password = $this->app['helper']('CryptoGraphy')->md5encrypt($Password);
		$User = SoftwareUser::where('email','=',$userName)
							->where('password', '=', $Password)
							->get();

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
	
	public function getUserinfo($identify,$idCompany){
		
		$this->app['helper']('ModelLog')->Log();
		$User = SoftwareUser::select('software_user.*','company_user.*','company_details.rand_email','company_details.timezone')
								->where('software_user.identify','=',$identify)
								->where('company_user.id_company','=',$idCompany)
								->leftjoin('company_user','software_user.identify','=','company_user.user_identify')
								->leftjoin('company_details','company_user.id_company','=','company_details.id')
								->get(); 
		
		if(isset($User[0]) && $this->app['helper']('Utility')->notEmpty($User[0])){
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
	
}