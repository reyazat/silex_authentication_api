<?php
//Extension Twig Service Provider
if(!$this->app['debug'])$cache = $this->app['config']['cache_dir'].'Twig/'; else $cache = false;

$this->app['twig.options'] = array('cache' => $cache, 'debug' => $this->app['debug'] , 'strict_variables' => false);

$this->app['twig']->addExtension(new \nochso\HtmlCompressTwig\Extension(true));

$filter = new Twig_SimpleFilter('decode', function ($string) {
	
    return self::Helper('CryptoGraphy')->md5decrypt($string);
});
$this->app['twig']->addFilter($filter); 

$filter = new Twig_SimpleFilter('encode', function ($string) {
    return self::Helper('CryptoGraphy')->md5encrypt($string);
});

$this->app['twig']->addFilter($filter); 

$filter = new Twig_SimpleFilter('loader', function ($path , $func , $option) {

	$res = self::load($path);
	
	return call_user_func_array(array($res,$func),$option);
});
	
$this->app['twig']->addFilter($filter);

/*************************Extension Translation**************************************/
	
$this->app['translator']->addLoader('yaml', new Symfony\Component\Translation\Loader\YamlFileLoader());

$finder = new Symfony\Component\Finder\Finder();

$finder->files()->name('*.yml')->in($this->app['config']['trans_path']);
foreach ($finder as $file) { 
	$this->app['translator']->addResource('yaml', $file->getRealpath(), $file->getRelativePath());
}
   
///end