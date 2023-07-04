<?php
include_once BASEDIR . '/App/Config.php';
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class - Bootstrap
 * @category Bootstrap
 * @package  app\
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 */
class Bootstrap extends Config {
    /**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app;
	
    /**
     * Constructor
     * 
     */
    public function __construct() {
        parent::__construct();
        // Start event 'eApp'
        $this->app['watch']->start('eApp');
		self::errorException();
		include_once $this->app['baseDir'] . '/App/Session.php';
        // Routes & middlewares
		include_once $this->app['baseDir'] . '/App/Routes.php';
		// Init providers
		include_once $this->app['baseDir'] . '/App/Register.php';
		include_once $this->app['baseDir'] . '/App/Extension.php';
		//end
		include_once $this->app['baseDir'] . '/App/Logger.php';


		//------ WATCH-Bootstrap --------//
        $this->app['watch']->lap('eApp');
		
		$this->app['load'] = $this->app->protect(function($class_name){return self::load($class_name);});
		$this->app['helper'] = $this->app->protect(function($class_name){return self::Helper($class_name);});
		$this->app['component'] = $this->app->protect(function($class_name){return self::Component($class_name);});
		
    }
	
	private function load($hash) {
		$tags = explode("_", $hash);
		$newadd = implode("\\" , $tags);
		return new $newadd($this->app);
	}
	public function Helper($hash) {
		
		return self::load('Helper_'.$hash);
	}
	public function Component($hash) {
		
		return self::load('Component_'.$hash);
	}
	
	public function errorException() {
		
		// redirect to 404 page if rout not found
		$this->app->error(function (\Exception $e, $request) {
			$code = $errorMessage = $requestUrl = $getMethod= '';
			// error code
			if(method_exists($e, 'getStatusCode')) $code = $e->getstatusCode();
			// error message
			if(method_exists($e, 'getmessage')) $errorMessage = $e->getmessage();
			// error message
			if(method_exists($e, 'getMethod')) $getMethod = $e->getMethod();
			// request url
			if(method_exists($e, 'getrequestUri')) $requestUrl = $request->getrequestUri();

			$this->app['monolog.debug']->warning($errorMessage,['code'=>$code,'Request Url'=>$requestUrl]);
			//return new RedirectResponse('/error/'.$code);
			if(!$this->app['debug']){
				switch ($code) {
				case 404:
					$message = 'Sorry, the page you are looking for could not be found.';
				break;
						
				case 401:
					$message = 'Access is denied due to invalid credentials.';
				break;
						
				case 405:
					$message = 'The requested resource does not support http method `'.$getMethod.'`.';
					
				break;
						
				case 408:
					$message = 'Request Timed Out.';
					
				break;
						
						
				//default:$message = 'We are sorry, but something went terribly wrong.';Whoops, looks like something went wrong
				}
				return new Response($message, $code);
			}

		});


	}

	public function setapp($key,$value) {
		$this->app[$key] = $value;
    }
	public function getapp($key='') {
		if(!empty($key)){
			return $this->app[$key];
		}else{
			return $this->app;
		}
    }
	/**
     *  Run this application
     */
    public function run() {
        // Run app
		if ($this->app['debug']) {
			$this->app->run();
		}
		else{
			$this->app['http_cache']->run();
		}

    }
}