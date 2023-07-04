<?php 
namespace Helper;

use Helper\ArrayFunc;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use \Colors\RandomColor;

class Utility{
	
	public function isNumber($_value){
        return is_numeric($_value);
    }
	
	public function getLength($_value){
		
		return strlen($_value);
		
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
	
	public function closetag($html){ 
		
		$html5 = new HTML5();
		$dom = $html5->loadHTML($html);

		// Render it as HTML5:
		return $html5->saveHTML($dom);

	}
	
	public function isEmail($email){
		
		$email = $this->secureInput($email);
		$valid = new \LayerShifter\TLDDatabase\Store();
		if($this->validateAddress($email)){
			$domain = explode('@',$email);
			$suffix = explode('.',$domain[1]);
			if($valid->isExists($suffix[1])){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
		
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
	public function isValidDomain($domain){
		
		return $this->validateDomain($this->secureInput($domain));
		
	}
	
	public function isDate($date)
	{
	    return (bool)strtotime($date);
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
		
		$fs = new Filesystem();
		return $fs->exists($src);
		
    }
	
	public function createPath($path) {
		
		$fileSystem = new Filesystem();
		$checkExist = self::checkFile($path);
		if(!$checkExist){
			$fileSystem->mkdir($path);
		}
		
		return true;
	  /*if (is_dir($path)) return true;
	  $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
	  $return = self::createPath($prev_path);
	  return ($return && is_writable($prev_path)) ? self::createDir($path) : false;*/
		
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
           /* @unlink($src);*/
			$fs = new Filesystem();
			$fs->remove($src);
        }
        return true;
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
	
	public function savefiletxt($path = NULL, $content = NULL){
		 
        $content = stripcslashes($content);
		$fs = new Filesystem();
		try {
			$fs->dumpFile($path, $content);
		} catch (IOExceptionInterface $e) {
			echo "An error occurred while creating your directory at ".$e->getPath();
		}
		
    }
	
	public function readFile($path){
		
		$fileName = self::getName($path);
		$filePath = str_replace($fileName,'',$path);
		
		$finder = new Finder();
		$finder->files()->in($filePath)->name($fileName);
		$contents = '';
		foreach ($finder as $file) {
			$contents = $file->getContents();
			break;
		}
		return $contents;
		
	}
	public function secureInput($str)
    {	$str = $this->trm(preg_replace('/<(.)s(.)c(.)r(.)i(.)p(.)t/i', '', $str));
		$str = $this->trm(preg_replace('/[\r\n]+/', '', $str));
		$str = $this->trm(str_replace(array("\r", "\n"), '', $str));
		$str = $this->trm(str_replace(array(";"), '', $str));
		$str = strip_tags($str);
        return $str;
    }
	public function compressHtml($buffer){

		$search = array(
			'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
			'/[^\S ]+\</s',     // strip whitespaces before tags, except space
			'/(\s)+/s',         // shorten multiple whitespace sequences
			'/<!--(.|\s)*?-->/' // Remove HTML comments
		);

		$replace = array(
			'>',
			'<',
			'\\1',
			''
		);

		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
		
	}
	
	public function extractString($string, $start, $end) {
		
		$string = " ".$string;
		$ini = strpos($string, $start);
		if ($ini == 0) return "";
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
		
	}
	
	public function formatBytes($bytes, $precision = 2) { 
		
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		$bytes /= (1 << (10 * $pow)); 

		return round($bytes, $precision) . ' ' . $units[$pow]; 
		
	} 
	
	public function stripSlashes($content){
		
		return stripslashes($content);
		
	}
	
	public function stripTags($html){
		
		// Strip HTML Tags
		$clear = strip_tags($html);
		// Clean up things like &amp;
		$clear = html_entity_decode($clear);
		// Strip out any url-encoded stuff
		$clear = urldecode($clear);
		// Replace non-AlNum characters with space
		$clear = preg_replace('/[^A-Za-z0-9]/', ' ', $clear);
		// Replace Multiple spaces with single space
		$clear = preg_replace('/ +/', ' ', $clear);
		// Trim the string of leading/trailing space
		$clear = trim($clear);
		
		return $clear; 
		
	}
	
	public function avatarFromName($name,$length = 2){
				
		$color = RandomColor::one();
		
		$avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
		$image = $avatar->name($name)->length($length)->background($color)->generate(); 
		return base64_encode($image->stream('png', 100));
		
	}
	
	public function truncate($string,$length){
		
		return substr($string, 0, $length); 
		
	}
	
	public function decodeBase64($encode){
		return base64_decode($encode);
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
	
	public function cleanName($string) {
		$string = str_replace(array('[\', \']'), '', $string);
		$string = preg_replace('/\[.*\]/U', '', $string);
		$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
		$string = preg_replace(array('/[^a-zA-Z0-9\/_|+ .-]/', '/[-]+/') , '-', $string);
		$string = str_replace(';','',$string);
		return $string;
	}
	
	
	/**
     * @return boolean
     * @static
     * @access public
     */
	public static function validateDomain($domain)
	{
		return checkdnsrr($domain); // returns TRUE/FALSE;
	}
	
	  /**
     * Check that a string looks like an email address.
     * @param string $address The email address to check
     * @param string|callable $patternselect A selector for the validation pattern to use :
     * * `auto` Pick best pattern automatically;
     * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0, PHP >= 5.3.2, 5.2.14;
     * * `pcre` Use old PCRE implementation;
     * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL;
     * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
     * * `noregex` Don't use a regex: super fast, really dumb.
     * Alternatively you may pass in a callable to inject your own validator, for example:
     * validateAddress('user@example.com', function($address) {
     *     return (strpos($address, '@') !== false);
     * });
     * @return boolean
     * @static
     * @access public
     */
	public static function validateAddress($address, $patternselect = null)
    {
        if (is_null($patternselect)) {
            $patternselect = 'auto';
        }
       
        //Reject line breaks in addresses; it's valid RFC5322, but not RFC5321
        if (strpos($address, "\n") !== false or strpos($address, "\r") !== false) {
            return false;
        }
        if (!$patternselect or $patternselect == 'auto') {
            //Check this constant first so it works when extension_loaded() is disabled by safe mode
            //Constant was added in PHP 5.2.4
            if (defined('PCRE_VERSION')) {
                //This pattern can get stuck in a recursive loop in PCRE <= 8.0.2
                if (version_compare(PCRE_VERSION, '8.0.3') >= 0) {
                    $patternselect = 'pcre8';
                } else {
                    $patternselect = 'pcre';
                }
            } elseif (function_exists('extension_loaded') and extension_loaded('pcre')) {
                //Fall back to older PCRE
                $patternselect = 'pcre';
            } else {
                //Filter_var appeared in PHP 5.2.0 and does not require the PCRE extension
                if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
                    $patternselect = 'php';
                } else {
                    $patternselect = 'noregex';
                }
            }
        }
        switch ($patternselect) {
            case 'pcre8':
                /**
                 * Uses the same RFC5322 regex on which FILTER_VALIDATE_EMAIL is based, but allows dotless domains.
                 * @link http://squiloople.com/2009/12/20/email-address-validation/
                 * @copyright 2009-2010 Michael Rushton
                 * Feel free to use and redistribute this code. But please keep this copyright notice.
                 */
                return (boolean)preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $address
                );
            case 'pcre':
                //An older regex that doesn't need a recent PCRE
                return (boolean)preg_match(
                    '/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\[ -~]|[^"])"?){65,}@)(?>' .
                    '[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*")' .
                    '(?>\.(?>[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*"))*' .
                    '@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\.(?![a-z0-9-]{64,})' .
                    '(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:' .
                    '[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?' .
                    '::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:' .
                    '[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?' .
                    '::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\])$/isD',
                    $address
                );
            case 'html5':
                /**
                 * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
                 * @link http://www.whatwg.org/specs/web-apps/current-work/#e-mail-state-(type=email)
                 */
                return (boolean)preg_match(
                    '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                    $address
                );
            case 'noregex':
                //No PCRE! Do something _very_ approximate!
                //Check the address is 3 chars or longer and contains an @ that's not the first or last char
                return (strlen($address) >= 3
                    and strpos($address, '@') >= 1
                    and strpos($address, '@') != strlen($address) - 1);
            case 'php':
            default:
                return (boolean)filter_var($address, FILTER_VALIDATE_EMAIL);
        }
    }
	
	
	
}
