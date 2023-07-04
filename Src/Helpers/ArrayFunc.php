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
		
		$res = array_unique($out);
		return $res;
		
	}
	
	public function arrayminus($arr1 = [],$arr2 = []){
		
		$out = [];
		$arr1 = array_filter($arr1);
		$arr2 = array_filter($arr2);
		$out = array_diff($arr1, $arr2);
		$out = array_unique($out);
		
		return array_filter($out);
		
	}
	
	public function sortMultiDimensionalArr($arr,$sortField){
		
		usort($arr, function($a, $b) {
			return $a['order'] - $b['order'];
		});
		
		return $arr;
		
	}
	
	public function array_find_deep($array, $search,$keyIdentify, $keys = array()){
		
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$sub = self::array_find_deep($value, $search, array_merge($keys, array($key)));
				if (count($sub)) {
					return $sub;
				}
			} elseif ($value === $search && $key == $keyIdentify) {
				return array_merge($keys, array($key));
			}
		}

		return array();
		
	}
	
	public function groupBy($array,$groupKey,$speceficKey=''){
		
		$groupArr = [];
		foreach($array as $row){
			
			$groupArr[$row[$groupKey]][] = (!empty($speceficKey))?$row[$speceficKey]:$row;
			
		}
		
		return $groupArr;
		
	}
	
	public function array_orderby(){
		
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
					$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
				}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}
	
	public function stringToArray($str,$spliter = ','){
		
		$arr = [];
		$arr = explode($spliter,$str);
		$arr = array_unique($arr);
		$arr = array_filter($arr);
		
		return $arr;
		
	}
	
	public function cleanArray($arr = []){
		
		$arr = array_unique($arr);
		$arr = array_filter($arr);
		
		return $arr;
		
	}
	
}