<?php


$this->app['translator']->addLoader('yaml', new Symfony\Component\Translation\Loader\YamlFileLoader());

$finder = new Symfony\Component\Finder\Finder();

$finder->files()->name('*.yml')->in($this->app['config']['trans_path']);
foreach ($finder as $file) { 
	$this->app['translator']->addResource('yaml', $file->getRealpath(), $file->getRelativePath());
}
