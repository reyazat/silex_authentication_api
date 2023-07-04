<?php
/********
$this->app['predis']['db']->set('db', 'database');
$this->app['predis']['session']->set('session', 'session');
$this->app['predis']['cache']->set('cache', 'caching');

********/
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


$this->app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => $this->app['config']['cache_dir'].'Http/',
));


$this->app->register(new Silex\Provider\SessionServiceProvider());
$this->app['session.storage.save_path'] = $this->app['config']['cache_dir'].'Sesstions/';
$this->app['session.storage.options'] = [
   // expire in 5 hour
   'cookie_lifetime' => 18000
  ];
$this->app['session']->start();
