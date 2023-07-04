<?php
namespace Helper;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class CryptoGraphy{
	
	public function __construct() {
       	
		if (!function_exists("openssl_encrypt")) {
			dumper("openssl function openssl_encrypt does not exist");
		}
		if (!function_exists("hash")) {
			dumper("function hash does not exist");
		}
	}

	public function encryptPassword( $string ) {
    	return password_hash($string,PASSWORD_BCRYPT);
    }
    public function verifyPassword( $string ,$password ) {
    	return password_verify($string,$password);
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
	
	public function randomPassword($length = 8) {
		
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@!$-';
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
				$payLoad = ['status'=>'success','uuid'=>$uuid1->toString()]; // i.e. e4eaaaf2-d142-11e1-b3e4-080027620cdd
				
			}else if($v == 3){
				
				// Generate a version 3 (name-based and hashed with MD5) UUID object
				$uuid3 = Uuid::uuid3(Uuid::NAMESPACE_DNS, $name);
				$payLoad = ['status'=>'success','uuid'=>$uuid3->toString()]; // i.e. 11a38b9a-b3da-360f-9353-a5a725514269
				
			}else if($v == 4){
				
				// Generate a version 4 (random) UUID object
				$uuid4 = Uuid::uuid4();
				$payLoad = ['status'=>'success','uuid'=>$uuid4->toString()]; // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
				
			}else if($v == 5){
				
				// Generate a version 5 (name-based and hashed with SHA1) UUID object
				$uuid5 = Uuid::uuid5(Uuid::NAMESPACE_DNS, $name);
				$payLoad = ['status'=>'success','uuid'=>$uuid5->toString()]; // i.e. c4a760a8-dbcf-5254-a0d9-6a4474bd1b62
				
			}


		} catch (UnsatisfiedDependencyException $e) {

			// Some dependency was not met. Either the method cannot be called on a
			// 32-bit system, or it can, but it relies on Moontoast\Math to be present.
			$payLoad = ['status'=>'error','message'=>$e->getMessage()];

		}
		
		return $payLoad;
		
	}
	
}