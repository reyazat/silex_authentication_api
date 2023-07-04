<?php 
namespace Helper;

use Helper\ArrayFunc;

class Utility{
	
	public function isNumber($_value){
        return is_numeric($_value);
    }
	
	public function getLength($_value){
		
		return strlen($_value);
		
	}
	
	public function isArray($_value){
		return is_array($_value);
	}
	
	public function convertResponseToArray($response){

		$content = '';
		$content = $response->getcontent();

		if (strpos($content,'Content') !== false) {
			
			if(strpos($content,'Content') == 0){
				
				$content = str_replace('Content','',$content);
				
			}

		}
		
		if(self::isJSON($content)){
			
			return self::decodeJson($content,true);
			
		}else{
			
			return $content;
			
		}
		
		
	}
	
	public function isJSON($string){
		
	   return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
		
	}
	
	public function isEmail($email){
		
		return filter_var($email, FILTER_VALIDATE_EMAIL);
		
	}
	
	public function decodeJson($data,$array = true){
		
		return json_decode($data,$array);
		
	}
	
	public function encodeJson($data){
		
		return json_encode($data);
		
	}
	
	public function notEmpty($data){
		$ArrayFunc = new ArrayFunc();
		if($ArrayFunc->isArray($data)){
			
			$data = array_filter($data);
			
		}else{
			
			$data = self::trm($data);
			
		}
		
		
		return !empty($data);
		
	}
	
	public function is_set($data){
		
		return isset($data);
		
	}
	
	public function trm($val){
		
		return trim($val);
		
	}
	
	public function isAjax(){
		
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			
			return TRUE;
			
		}else{
			
			return FALSE;
			
		}
		
	}
	
	public function upload($file_info = NULL, $dest = NULL){
		
        $media_info = array();
		
        // Sort File Information
        $tmpfile = $file_info['tmp_name'];
        $tmpfilename = $file_info['name'];
        $tmpfilesize = $file_info['size'];

        // Name And Extensions
        $name = substr($tmpfilename, 0, (strrpos($tmpfilename, '.')));
        $ext = substr($tmpfilename, strrpos($tmpfilename, '.') + 1, strlen($tmpfilename));

        // Reformat the file name
        if (self::checkFile("{$dest}{$tmpfilename}")) {
            $c = date(time()) . rand(1, 10000);
            $media_info['file_name'] = $name . "_" . $c . "." . $ext;
            $media_info['file_name2'] = $name . "_" . $c;
			
        }
        else {
            $media_info['file_name'] = "{$name}.{$ext}";
            $media_info['file_name2'] = "{$name}";
        }
		$media_info['extention'] = $ext;
        if (isset($tmpfile) && is_uploaded_file($tmpfile)) {
			move_uploaded_file($tmpfile, "{$dest}{$media_info['file_name']}");
			return $media_info;
        }
		else{
			return false;
		}

    }
	
	public function checkFile($src = ""){
		
        return file_exists($src);
		
    }
	
	public function createPath($path) {
		
		  if (is_dir($path)) return true;
		  $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
		  $return = self::createPath($prev_path);
		  return ($return && is_writable($prev_path)) ? self::createDir($path) : false;
		
	}
	
	public function createDir($path = "", $permisson = 0777, $recursive = false){
		
        if (!self::checkFile($path)) {
			
            @mkdir($path, $permisson, $recursive);
        }

        @chmod($path, $permisson);

        return $this;
    }
	
	public function deleteFile($src = NULL){
		
        if (self::checkFile($src)) {
            @unlink($src);
        }
        return $this;
    }
	
	public function getName($filePath){
		
        return basename($filePath);
    }
	
	public function getExt($filePath){
		
		return pathinfo($filePath, PATHINFO_EXTENSION);
		
	}
	
	public function lastModify($filePath){
		
		return filemtime($filePath);
		
	}
	
	public function savefiletxt($path = NULL, $content = NULL, $mode='w'){
		 
        $content = stripcslashes($content);

        if (!$handle = fopen($path, $mode)) {
            /*echo "Cannot open file ($filename)";
            exit;*/ 
			return "Cannot open file ($filename)";
        }
        if (fwrite($handle, $content) === FALSE) {
            /*echo "Cannot write to file ($filename)";
            exit;*/
			return "Cannot write to file ($filename)";
        }

        fclose($handle);
    }
	
	public function slug($string){
		$string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
   		$string = preg_replace('/[^A-Za-z0-9\-\.]/', '', $string); // Removes special chars.
		return strtolower($string);
	}
	
	public function secureInput($str)
    {
        $str = $this->trm(preg_replace('/[\r\n]+/', '', $str));
		$str = $this->trm(str_replace(array("\r", "\n"), '', $str));
		$str = $this->trm(str_replace(array(";"), '', $str));
		$str = strip_tags($str);
        return $str;
    }
	
	public function mysql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}

		return $inp;
	} 
	
	public function mysql_escape_mimic_inverse($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), $inp);
		}

		return $inp;
	}
	public function clearField($value){
		
		$newVal = '';
		if(!is_array($value)){
			//$newVal = $this->app->escape($value);
			$newVal = strip_tags($value);
			$newVal = trim($newVal);
			$newVal = preg_replace('/\s+/', ' ', $newVal);
			$newVal = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $newVal);
			$newVal = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $newVal);
			$newVal = preg_replace ( '/<(.)s(.)c(.)r(.)i(.)p(.)t/i','',$newVal );
			$newVal = str_replace(';','',$newVal);
			//$newVal = filter_var($newVal, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_LOW|FILTER_FLAG_STRIP_HIGH);
			$newVal = htmlspecialchars_decode($newVal);
			//$newVal = $this->app['capsule']->connection()->getPdo()->quote($newVal);
			$newVal = self::mysql_escape_mimic($newVal);
		}else{
			$newVal = $value;
		}
		
		return $newVal;
		
	} 
	
}
