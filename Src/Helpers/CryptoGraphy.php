<?php
namespace Helper;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Hashids\Hashids;
use Defuse\Crypto\File;
use Symfony\Component\Security\Csrf\CsrfToken;

class CryptoGraphy{
	
	protected $app;
	
	public function __construct($app) {
       	
		$this->app = $app;
		
		if (!function_exists("openssl_encrypt")) {
			dumper("openssl function openssl_encrypt does not exist");
		}
		if (!function_exists("hash")) {
			dumper("function hash does not exist");
		}
	}
	
	public function hashEncode($q , $key='AaBbTqJB0rcGDtEIfng5HMiUjBk1lxmGn0o3peqfrysCtpu4v4w1xFyZ' , $length=8){
		
		$hashids = new Hashids($key , $length);
		return $hashids->encode($q); 
	}
	public function hashDecode($q , $key='AaBbTqJB0rcGDtEIfng5HMiUjBk1lxmGn0o3peqfrysCtpu4v4w1xFyZ' , $length=8){
		
		$hashids = new Hashids($key , $length);
		return $hashids->decode($q); 
	}
	
	public function base64Decode($string){
		
		return base64_decode($string);
		
	}
	
	public function urlsafe_b64encode($string) {
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}

	public function urlsafe_b64decode($string) {
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
	/*
	 * md5 encrypt
	 * product by: AfMa
	 */
    public function md5encrypt( $q, $key=FALSE ) {
		///mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) )
        $cryptKey  = $key ? $key : 'TqJB0rGtIn5MUB1xG03efyCp441F';
		$key = hash('sha256', $cryptKey);
		$encrypt_method = "AES-256-CBC";
		$secret_iv = '9IEJWQDJE3-123.DASW1';
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		
        $qEncoded      = base64_encode( openssl_encrypt($q, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv ) );
        return( $qEncoded );
		
    }
	
	public function md5EncryptHash( $q, $key=FALSE ) {
       
        $cryptKey  = $key ? $key : 'TqJB0rGtIn5MUB1xG03efyCp441F';
        $key = hash('sha256', $cryptKey);
        $encrypt_method = "AES-256-CBC";
        $secret_iv = '9IEJWQDJE3-123.DASW1';
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
        $encoded_temp = openssl_encrypt($q, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv );
        
        $qEncoded = self::urlsafe_b64encode($encoded_temp);
        
        return( $qEncoded );
            
    }

	/*
	 * md5 decrypt
	 * product by: AfMa
	 */
    public function md5decrypt( $q, $key=FALSE ) {
		
		////mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) )
        $cryptKey  = $key ? $key : 'TqJB0rGtIn5MUB1xG03efyCp441F';
		$key = hash('sha256', $cryptKey);
		$encrypt_method = "AES-256-CBC";
		$secret_iv = '9IEJWQDJE3-123.DASW1';
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		
        $qDecoded      = rtrim( openssl_decrypt( base64_decode( $q ), $encrypt_method, $key, OPENSSL_RAW_DATA, $iv ), "\0");
        return( $qDecoded );
		
    }
	
	public function encryptPassword( $string ) {
    	return password_hash($string,PASSWORD_BCRYPT);
    }
	
    public function verifyPassword( $string ,$password ) {
    	return password_verify($string,$password);
    }
	
	public function randomPassword($length = 8) {
		
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@!$';
		$pass = array();
		$alphaLength = strlen($alphabet) - 1; 
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		//turn the array into a string and encrypt it
		return implode($pass); 
		
	}
	
	public function createUUID($v = 1,$name = 'php.net'){
		
		$payLoad = [];
		try {
			
			if($v == 1){
				
				// Generate a version 1 (time-based) UUID object
				$uuid1 = Uuid::uuid1();
				$payLoad = ['status'=>'Success','uuid'=>$uuid1->toString()]; // i.e. e4eaaaf2-d142-11e1-b3e4-080027620cdd
				
			}else if($v == 3){
				
				// Generate a version 3 (name-based and hashed with MD5) UUID object
				$uuid3 = Uuid::uuid3(Uuid::NAMESPACE_DNS, $name);
				$payLoad = ['status'=>'Success','uuid'=>$uuid3->toString()]; // i.e. 11a38b9a-b3da-360f-9353-a5a725514269
				
			}else if($v == 4){
				
				// Generate a version 4 (random) UUID object
				$uuid4 = Uuid::uuid4();
				$payLoad = ['status'=>'Success','uuid'=>$uuid4->toString()]; // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
				
			}else if($v == 5){
				
				// Generate a version 5 (name-based and hashed with SHA1) UUID object
				$uuid5 = Uuid::uuid5(Uuid::NAMESPACE_DNS, $name);
				$payLoad = ['status'=>'Success','uuid'=>$uuid5->toString()]; // i.e. c4a760a8-dbcf-5254-a0d9-6a4474bd1b62
				
			}


		} catch (UnsatisfiedDependencyException $e) {

			// Some dependency was not met. Either the method cannot be called on a
			// 32-bit system, or it can, but it relies on Moontoast\Math to be present.
			$payLoad = ['status'=>'Error','message'=>$e->getMessage(), 'code'=>$e->getCode()];

		}
		
		return $payLoad;
		
	}
	
	public function encryptFile($input,$destination,$fileName = ''){
		
		$payLoad = [];
		
		if(!$this->app['helper']('Utility')->notEmpty($input) || 
		   !$this->app['helper']('Utility')->notEmpty($destination)){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Source file, Destination'));
			$payLoad = ['status'=>'Error','message'=>$msg, 'code'=>400];
			
		}else{
			
			$isDir = is_dir($destination);
			if(!$isDir){
				
				$payLoad = ['status'=>'Error','message'=>'Destination must be a dir', 'code'=>404];
				
			}else{

				$password = $this->app['config']['parameters']['mysql_params']['key'];
				if(!$this->app['helper']('Utility')->notEmpty($fileName)){
					$fileName = $this->app['helper']('Utility')->getName($input);
				}
				
				$fileName  = $fileName . '.crypto';

				File::encryptFileWithPassword($input, $destination.$fileName, $password);
				$payLoad = ['status'=>'Success','message'=>'', 'code'=>200, 'data'=>['name'=>$fileName]];
				
			}
			
		}

		return $payLoad;
		
	}
	
	public function decryptFile($input,$destination,$fileName = ''){
		
		$payLoad = [];
		
		if(!$this->app['helper']('Utility')->notEmpty($input) || 
		   !$this->app['helper']('Utility')->notEmpty($destination)){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Source file, Destination'));
			$payLoad = ['status'=>'Error','message'=>$msg, 'code'=>400];
			
		}else{
			
			$isDir = is_dir($destination);
			if(!$isDir){
				
				$payLoad = ['status'=>'Error','message'=>'Destination must be a dir','code'=>404];
				
			}else{
				
				$password = $this->app['config']['parameters']['mysql_params']['key'];
				
				if(!$this->app['helper']('Utility')->notEmpty($fileName)){
					$fileName = $this->app['helper']('Utility')->getName($input);
					$fileName  = preg_replace('#\.crypto$#','',$fileName);
				}
				
				File::decryptFileWithPassword($input, $destination.$fileName, $password);
				$payLoad = ['status'=>'Success','message'=>'', 'code'=>200, 'data'=>['name'=>$fileName]];

			}
			
		}

		return $payLoad;
		
	}
	
	public function csrfToken($token_id){

		$csrfToken = $this->app['csrf.token_manager']->getToken($token_id); //'TOKEN'
		return $csrfToken;
		
	}
	
	public function loginUserCsrf($idCompany = ''){
		
		$csrfToken = '';
		$session = $this->app['request_content']->getSession();
		if ( $session->has('SoftWareUser')){
			$userDetails = $this->app['session']->get('SoftWareUser');
			$idComp = '';
			if($this->app['helper']('Utility')->notEmpty($idCompany)){
				$idComp = $idCompany;
			}else if(isset($userDetails['id_company']) && $this->app['helper']('Utility')->notEmpty($userDetails['id_company'])){
				$idComp = $userDetails['id_company'];
			}
			if(isset($userDetails['user_identify']) && 
			   $this->app['helper']('Utility')->notEmpty($userDetails['user_identify']) && 
			   $this->app['helper']('Utility')->notEmpty($idComp)){
				
				$getCsrf = self::csrfToken($idComp.'-'.$userDetails['user_identify'].date('Ymd'));
				$csrfToken = $getCsrf->getValue();
				
			}
		}
		
		return $csrfToken;
		
	} 
	
	public function validateLoginUserCsrf($token){
		
		$userDetails = $this->app['session']->get('SoftWareUser');
		$validateAccess = $this->app['csrf.token_manager']->isTokenValid(new CsrfToken($userDetails['id_company'].'-'.$userDetails['user_identify'].date('Ymd'),$token));
		
		return $validateAccess;
		
	}
	
}