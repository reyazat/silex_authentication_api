<?php
include_once BASEDIR . '/App/App.php';
include_once BASEDIR . '/App/Globalfunc.php';

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;


class Config  extends App{
	
    public function __construct() {
        parent::__construct();
		
		$this->_iniConfig();
		$this->_iniset();
		
		
		
    }
	public function _iniConfig() {
		$this->app['config'] = function ()  {
			$params = array();
			//---------------------
			# Set URL of the site
			$params['http'] = (isset($_SERVER['REDIRECT_HTTPS']) OR isset($_SERVER['HTTPS'])) ? "https://" : "http://";
			$params['host'] = $_SERVER['SERVER_NAME'];
			$params['port'] = $_SERVER['SERVER_PORT'];
			$params['subpath'] = dirname($_SERVER["PHP_SELF"]);
			
        	$params['base_url'] =($params['port'] != "80" && $params['port'] != "443") ? ($params['http'].$params['host'] . ":" . $params['port'] . $params['subpath']) : ($params['http'].$params['host'] . $params['subpath']);
			
			$params['base_dir'] = BASEDIR;
			$params['log_dir'] = BASEDIR . "/Logs/";
			$params['cache_dir'] = BASEDIR . "/Cache/";
			$params['twig_path'] = BASEDIR . "/Src/View/";
			$params['trans_path'] = BASEDIR .'/App/Config/Translation/';
			
			$fileConfig = is_file(BASEDIR . '/env.yml') ? BASEDIR . '/env.yml' : BASEDIR . '/App/Config/parameters.yml';
			
			$fs = new Symfony\Component\Filesystem\Filesystem();
			if($fs->exists(array($fileConfig))){
				try {
					$data = Yaml::parse(file_get_contents($fileConfig));
				
				} catch (ParseException $e) {
				
					echo $this->app->json("Internal Server Error (YAML SECTION) : ".$e->getMessage(), 500);exit;
				
				}
			}else{
				
					echo $this->app->json("Internal Server Error (YAML SECTION)", 500);exit;
			}
			
			foreach ($data as $keys => $values) {
				if(is_array($values)){
					foreach ($values as $key => $value) {
						$params[$keys][$key] = $this->_formatIniValue($value);
					}
				}else{
						$params[$keys] = $this->_formatIniValue($values);
				}
			}
			
			return $params;
		};
				
		//Set debug option
		$this->app['debug'] = $this->app['config']['parameters']['debug'];
		//Set basepath option
		$this->app['baseDir'] = $this->app['config']['base_dir'];
		$this->app['baseUrl'] = $this->app['config']['base_url'];
		//Set Timezone
		if (isset($this->app['config']['parameters']['timezone'])) {
			date_default_timezone_set($this->app['config']['parameters']['timezone']);
		}
		// Registering the ErrorHandler
		// It converts all errors to exceptions, and exceptions are then caught by Silex
		ErrorHandler::register();
		ExceptionHandler::register($this->app['debug']);
	}
	
	
	public function _iniset(){
		if($this->app['debug']){
			// 1 display All errors // 0 Dont display All errors
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			// -1 Report all PHP errors // 0 Turn off all error reporting
			error_reporting(-1);
		}else{
			// 1 display All errors // 0 Dont display All errors
			ini_set('display_errors', 0);
			ini_set('display_startup_errors', 0);
			// -1 Report all PHP errors // 0 Turn off all error reporting
			error_reporting(0);
		}
	}
	/**
     *  Format value for ini config file
     * 
     * @param string $value
     * @return string|int|float|bool
     */
    private function _formatIniValue($value) {
        if ($value === 'true' || $value === 'yes') {
            $value = TRUE;
        }
        if ($value === 'false' || $value === 'no') {
            $value = FALSE;
        }
        if ($value === '') {
            $value = NULL;
        }
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                $value = floatval($value);
            } else {
                $value = intval($value);
            }
        }
        return $value;
    }
	
		
}
