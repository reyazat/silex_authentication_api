<?php
use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Translation\Loader as Loader;

class App {
    /**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app ;
    /**
     * Constructor
     * 
     */
    public function __construct() {
	
        $autoload = require_once BASEDIR . '/vendor/autoload.php';
        // Create app
        $app = new Silex\Application();
        $this->app = $app;
        $this->app['autoload'] = $autoload;
		
		 // Create 'stopwatch' object
        $this->app['watch'] = function () {
            return new Stopwatch();
        };
	}
	
	
}
