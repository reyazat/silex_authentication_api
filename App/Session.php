<?php
/********
$this->app['predis']['db']->set('db', 'database');
$this->app['predis']['session']->set('session', 'session');
$this->app['predis']['cache']->set('cache', 'caching');

********/
/*
$this->app->register(new Predis\Silex\ClientsServiceProvider(), array(
    'predis.clients' => array(
		'db' => array(
            'parameters' => array($this->app['config']['parameters']['redis_params']),
            'options' => array(
                'prefix' => 'db:'
            ),
        ),
        'cache' => array(
            'parameters' => array($this->app['config']['parameters']['redis_params']),
            'options' => array(
                'prefix' => 'cache:'
            ),
        ),
	  'session' => array(
            'parameters' => array($this->app['config']['parameters']['redis_params']),
            'options' => array(
                'prefix' => 'sessions:'
            ),
        ),
    ),
	'predis.default_client' => 'db',

));
*/



/********
$this->app['cache']->store('foo', 'bar');
stores a variable with a 1 minute lifetime
$this->app['cache']->store('foo', 'bar', 60);
fetch variable
echo $this->app['cache']->fetch('foo');
delete variable
$this->app['cache']->delete('foo');
clear all cached variables
$this->app['cache']->clear();

********/
$this->app->register(new Moust\Silex\Provider\CacheServiceProvider(), array(
    'cache.options'=> array(
		'driver' => 'file',
        'cache_dir' => $this->app['config']['cache_dir'].'apcu/'
    )
	
));

$this->app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => $this->app['config']['cache_dir'].'Http/',
));


$this->app->register(new Silex\Provider\SessionServiceProvider());
$this->app['session.storage.save_path'] = $this->app['config']['cache_dir'].'Sessions/';
$this->app['session.storage.options'] = [
   // expire in 5 hour 
	'name'=>'Oauth',
	'cookie_lifetime' => 18000,
	'cookie_httponly' => 'On',
	'use_strict_mode'=> 'On',
	'use_only_cookies'=>'On',
	'cookie_samesite' => 'Strict',
	'sid_length'=>128,
	'sid_bits_per_character'=>6,
	'hash_function'=>'sha512',
	'cookie_secure'=>'On'
  ];
$this->app['session']->start();
