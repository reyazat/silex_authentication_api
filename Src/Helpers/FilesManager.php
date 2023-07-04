<?php 
namespace Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class FilesManager{
	protected $app;
	
	public function __construct($app){		
		$this->app = $app;	
    }
	
	public function readContent($path = '' , $fileName = ''){
		$content = '';
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->name($fileName)->depth('== 0');
		$finder->files()->in($path);
		foreach ($finder as $file) {
			$content = $file->getContents();
		}
		return $content;
	}
	
	public function readDir($path = ""){
		$files = [];
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->in($path);
		foreach ($finder as $file) {
			$files[] = $file;
		}
		return $files;
    }
	
	public function existsDir($path = ""){
		
		$fileSystem = new Filesystem();

		return $fileSystem->exists($path);
    }
	
	public function existsFiles($files = []){
		
		$fileSystem = new Filesystem();

		return $fileSystem->exists($files);
    }
	
	public function copyFile($source , $newPath){
		
		$fileSystem = new Filesystem();
		try {
			
			$fileSystem->copy($source, $newPath, true);
			
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while copyFile your directory at ".$exception->getPath());		    
		}
		return true;
		
    }
	
	public function createDir($path = "", $permisson = 0777){
		
		$fileSystem = new Filesystem();

		try {
        	if (!$fileSystem->exists($path) ) {
		    	$fileSystem->mkdir($path,$permisson);
        	}
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while creating your directory at ".$exception->getPath());		    
		}
		return true;
    }
	
	public function renameDir($oldName , $newName){
		
		$fileSystem = new Filesystem();

		try {
		    $fileSystem->rename($oldName , $newName);
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while rename your directory at ".$exception->getPath());		    
		}
		return true;
    }
	
	public function deleteDir($path ,$file=NULL,$symlink='symlink')
    {
        $fileSystem = new Filesystem();
		try {
		    	$fileSystem->remove(array($symlink,$path , $file));        
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while deleting your directory at ".$exception->getPath());		    
		}
       return true; 
    }
	
	public function saveContent($path = NULL, $content = NULL)
    {	
    	$fileSystem = new Filesystem();
		
		$content = stripcslashes($content);
		
		try {
        	$fileSystem->dumpFile($path, $content);
			
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while htmlsaving your directory at ".$exception->getPath());		    
		}
       return true; 
       
    }
	
	public function saveText($path = NULL, $content = NULL)
    {	
    	$fileSystem = new Filesystem();
		
		$content = $this->app['helper']('Utility')->secureInput($content);
		
		try {
        	$fileSystem->dumpFile($path, $content);
			
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while text saving your directory at ".$exception->getPath());		    
		}
       return true; 
       
    }
	
	public function appendToFile($path = NULL, $content = NULL)
    {	
    	$fileSystem = new Filesystem();
		
		$content = $this->app['helper']('Utility')->secureInput($content);
		
		try {
        	$fileSystem->appendToFile($path, $content);
			
		} catch (IOExceptionInterface $exception) {

			$this->app['monolog.debug']->debug("An error occurred while append your directory at ".$exception->getPath());		    
		}
       return true; 
       
    }
    
}