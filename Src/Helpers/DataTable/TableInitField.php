<?php
namespace Helper\DataTable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

use Illuminate\Database\Query\Expression as raw;

class TableInitField{
	
	protected $app;
	
	public function __construct($app) {
		$this->app = $app;
	}

	public function requireTableData(){
		
		$requests = $this->app['request_content'];
		
		$data = [];
		$data['start'] = $requests->get('start');
		$data['length'] = $requests->get('length');
		$data['draw'] = $requests->get('draw');
		$data['search'] = (is_string($requests->get('search')))?json_decode($requests->get('search'),true):$requests->get('search');
		$data['order'] = (is_string($requests->get('order')))?json_decode($requests->get('order'),true):$requests->get('order');
		
		return $data;
		
	}
	
	
	public function decryptField($fieldName,$asName = ''){
		
		if(!$this->app['helper']('Utility')->notEmpty($asName)){
			$asName = $fieldName;
		}
		
		$select = '';
		$select = new raw("CAST(AES_DECRYPT(`".$fieldName."`,UNHEX(SHA2('".$this->app['config']['parameters']['mysql_params']['key']."',512))) AS CHAR(512)) `".$asName.'`');

		return $select;
		
	}
	
	public function extractEncryptedJsonField($fieldName,$key,$lable,$multi = true){
		
		if($multi == true){
			$extractor = '"$[*].'.$key.'"';
		}else{
			$extractor = '"$.'.$key.'"';
		}
		
		$select = '';
		$select = new raw('TRIM( BOTH \'"\' from JSON_EXTRACT(CAST(AES_DECRYPT(`'.$fieldName.'`,UNHEX(SHA2(\''.$this->app['config']['parameters']['mysql_params']['key'].'\',512))) AS CHAR(512)), '.$extractor.')) AS `'.$lable.'`');

		return $select;
		
	}
	
	public function extractJsonField($fieldName,$key,$lable,$multi = true){
		
		if($multi == true){
			$extractor = '"$[*].'.$key.'"';
		}else{
			$extractor = '"$.'.$key.'"';
		}
		
		$select = '';
		$select = new raw('TRIM( BOTH \'"\' from JSON_EXTRACT(`'.$fieldName.'`, '.$extractor.')) AS `'.$lable.'`');

		return $select;
		
	}
	
	public function encryptField($field){
		
		return new raw("AES_ENCRYPT('".$field."', UNHEX(SHA2('".$this->app['config']['parameters']['mysql_params']['key']."',512)))");
		
	}
	
	public function encryptFieldBase64($field){
		
		return new raw("AES_ENCRYPT(TO_BASE64('".$field."'), UNHEX(SHA2('".$this->app['config']['parameters']['mysql_params']['key']."',512)))");
		
	}
	
	public function decryptFieldBase64($fieldName,$asName = ''){
		
		if(!$this->app['helper']('Utility')->notEmpty($asName)){
			$asName = $fieldName;
		}
		
		$select = '';
		$select = new raw('FROM_BASE64(CAST(AES_DECRYPT(`'.$fieldName.'`,UNHEX(SHA2(\''.$this->app['config']['parameters']['mysql_params']['key'].'\',512))) as CHAR(512)))  `'.$asName.'`');

		return $select;
		
	}
	
	public function searchFieldByLike($fieldName,$search){

		$where = '';
		$where = 'CONVERT(AES_DECRYPT(`'.$fieldName.'`,UNHEX(SHA2(\''.$this->app['config']['parameters']['mysql_params']['key'].'\',512))) USING utf8) LIKE \'%'.$search.'%\'';
		
		return $where;
		
	}
	
	public function searchEncryptField($fieldName,$search){

		$where = '';
		$where = 'CONVERT(AES_DECRYPT(`'.$fieldName.'`,UNHEX(SHA2(\''.$this->app['config']['parameters']['mysql_params']['key'].'\',512))) USING utf8) = \''.$search.'\'';
		
		return $where;
		
	}
	
}