<?php
namespace Helper;

class ArrayFunc{
	
	// explode from multi delimiter
	public function explodeX( $delimiters, $string ){
    	return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
	}
	
	public function isArray($data){
		
		if (is_array($data)){
			return true;
		}else{
			return false;
		}
		
	}
	
		
	// usage in fetch from data base
	public function getSpeceficKey($data,$key,$method){
		$res = array();
		foreach($data as $row){
			$res[] = $row[$key];
		}

		if($method == 'array'){
			return $res;
		}
		else{
			return implode(',',$res);
		}
	}
	
	public function arrayplus( $arr1 = array(), $arr2 = array() ){
		
		$out = array();
		$arr1 = array_filter($arr1);
		$arr2 = array_filter($arr2);
		if(!empty($arr1) && !empty($arr2)) $out = array_merge($arr1, $arr2);
		else if(empty($arr1) && !empty($arr2)) $out = $arr2;
		else if(!empty($arr1) && empty($arr2)) $out = $arr1;
		$out = array_unique($out);
		return $out;
		
	}
	
	public function arrayminus($arr1 = [],$arr2 = []){
		
		$out = [];
		$arr1 = array_filter($arr1);
		$arr2 = array_filter($arr2);
		$out = array_diff($arr1, $arr2);
		$out = array_unique($out);
		
		return array_filter($out);
		
	}
	
}