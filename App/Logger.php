<?php
/*
	// $this->app['monolog']->info('My logger is now ready',array('testKey'=>'yoho'));
	// $this->app['monolog']->error('My logger is now ready',array('testKey'=>'wooow'));
	// $this->app['monolog']->warning('My logger is now ready',array('testKey'=>'wooow'));
	// $this->app['monolog']->debug('My logger is now ready',array('testKey'=>'wooow'));

	// chanel: DEBUG ,INFO ,NOTICE ,WARNING ,ERROR ,CRITICAL ,ALERT ,EMERGENCY 
	// DEBUG : 100 --> Detailed debug information.
	// INFO : 200 --> Interesting events. Examples: User logs in, SQL logs.
	// NOTICE : 250 --> Normal but significant events.
	// WARNING : 300 -->  Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
	// ERROR : 400 --> Runtime errors that do not require immediate action but should typically be logged and monitored.
	// CRITICAL : 500 --> Runtime errors that do not require immediate action but should typically be logged and monitored.
	// ALERT : 550 --> Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
	// EMERGENCY : 600 --> Emergency: system is unusable.

*/

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;

$this->app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.name' => 'app',
));


$this->app['monolog.factory'] = $this->app->protect(function ($name) {
    $log = new $this->app['monolog.logger.class']($name);
    $log->pushHandler($this->app['monolog.handler']);
    return $log;
});

foreach ($this->app['config']['parameters']['monolog']['chanels'] as $channel) {
    $this->app['monolog.'.$channel] = function () use ($channel) {
        return $this->app['monolog.factory']($channel);
    };
	
	$this->app['monolog.'.$channel] = function () use ($channel) {
		$log = new $this->app['monolog.logger.class']($channel);
		$handler = new RotatingFileHandler($this->app['config']['log_dir'].$channel.'.html', 30 , Logger::DEBUG);
		$handler->setFormatter(new HtmlFormatter);
		$log->pushHandler($handler);

		$handler = new FingersCrossedHandler($handler,Logger::ERROR);
		$log->pushHandler($handler);

		$handler = new FirePHPHandler();
		$log->pushHandler($handler);
/*
		$mailhandler = new NativeMailerHandler(
				$this->app['config']['parameters']['monolog']['mailer']['email'],
				$this->app['config']['parameters']['monolog']['mailer']['subject'],
				$this->app['config']['parameters']['monolog']['mailer']['from']
			);

		$dupicate = new DeduplicationHandler($mailhandler,$this->app['config']['log_dir'].'Duplicate/'.$channel.'.log',Logger::ERROR,300);
		$log->pushHandler($dupicate);
*/
		$log->pushProcessor(new WebProcessor());
		$log->pushProcessor(new IntrospectionProcessor());
		$log->pushProcessor(new MemoryUsageProcessor());
		$log->pushProcessor(new UidProcessor());

		return $log;
	};
}





$this->app->extend('monolog', function($monolog) {
	$handler = new RotatingFileHandler($this->app['config']['log_dir'].'monolog.html', 30 , Logger::DEBUG);
	$handler->setFormatter(new HtmlFormatter);	
	$monolog->pushHandler($handler);
	
	$handler = new FingersCrossedHandler($handler,Logger::ERROR);
	$monolog->pushHandler($handler);
	
	$handler = new FirePHPHandler();
	$monolog->pushHandler($handler);
	/*
	$mailhandler = new NativeMailerHandler(
				$this->app['config']['parameters']['monolog']['mailer']['email'],
				$this->app['config']['parameters']['monolog']['mailer']['subject'],
				$this->app['config']['parameters']['monolog']['mailer']['from']
			);
		
	$dupicate = new DeduplicationHandler($mailhandler,$this->app['config']['log_dir'].'Duplicate/monolog.log',Logger::ERROR,300);
	$monolog->pushHandler($dupicate);
*/
	
	
	$monolog->pushProcessor(new WebProcessor());
	$monolog->pushProcessor(new IntrospectionProcessor());
	$monolog->pushProcessor(new MemoryUsageProcessor());
	$monolog->pushProcessor(new UidProcessor());
    
		

    return $monolog;
});

	