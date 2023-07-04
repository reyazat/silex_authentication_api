<?php
namespace Helper;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;

use Assetic\Filter\UglifyJs2Filter;
use Assetic\Filter\UglifyCssFilter;
use Assetic\Filter\Yui;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Symfony\Component\Finder\Finder;


class Compress{
	
	protected $app;
	
	public function __construct($app){
		
		$this->app = $app; 
		
	}
	
	public function compression(){

		//$utility = new Utility();
		
		$args = func_get_args();
		
		$assets = [];
		foreach($args as $arg){
			
			// get name of file
			$fileName = '';
			$fileName = $this->app['helper']('Utility')->getName($arg);
		
			// get extension of file
			$type = '';
			$type = $this->app['helper']('Utility')->getExt($arg);
			
						
			// check if Cache Dir exist in Web folder in Root Dir
			$this->app['helper']('FilesManager')->createDir($this->app['baseDir'] . '/Web/Cache/Compress');
						
			// Make Compress file path
			$compressFilePath = $compressFileUrl = $filePath = '';
			$filePath = $this->app['baseDir'].'/Web/'.$arg;
			
			// get last time update of file
			$lastModify = $this->app['helper']('Utility')->lastModify($filePath);
			
			$compressFilePathWithoutVersion = $this->app['baseDir'] . '/Web/Cache/Compress/'.$arg;
			$compressFilePath = $compressFilePathWithoutVersion.'?v='.$lastModify;
			$compressFileUrl = $this->app['baseUrl'] . 'Cache/Compress/'.$arg.'?v='.$lastModify;
			
			// check compress file exist or not
			$checkAssetFileExist = $fs->exists($compressFilePath);
			//dumper($compressFilePath,$checkAssetFileExist,$this->app['debug']);
			if($type == 'css'){

				if($checkAssetFileExist) {
					
					$assets[] = self::makeCssLink($compressFileUrl);
					continue;
				}

				// compress css file
				$cssContent = '';
				$cssContent = self::compressCss($filePath);
				
				// save compress file
				self::makeCompressFile($compressFilePathWithoutVersion,$compressFilePath,$cssContent);
				$assets[] = self::makeCssLink($compressFileUrl);
				

			}elseif($type == 'js'){
				
				if($checkAssetFileExist) {
					
					$assets[] = self::makeJsLink($compressFileUrl);
					continue;
				}

				// compress js file
				$jsContent = '';
				$jsContent = self::compressJs($filePath);

				// save compress file
				self::makeCompressFile($compressFilePathWithoutVersion,$compressFilePath,$jsContent);
				$assets[] = self::makeJsLink($compressFileUrl);


			}else{
				continue;
			}

		}
		
		return implode(' ',$assets);
		
	}
	
	
	private function makeCompressFile($pathWithoutVersion,$path,$content){
		
		$fs = new Filesystem();
		$fs->dumpFile($pathWithoutVersion, $content);
		$fs->dumpFile($path, '');
		
		return true;
		
	}
	
	private function makeCssLink($url){
		
		$link = '';
		$link = '<link href="'.$url.'" rel="stylesheet" type="text/css">';
		
		return $link;
		
	}
	
	private function makeJsLink($url){
		
		$link = '';
		$link = '<script type="text/javascript" src="'.$url.'"></script>';
		
		return $link;
		
	}
	
	private function compressJs($file){
		
		$js = new AssetCollection(array(
			new FileAsset($file),
		), array(
			//new PackerFilter(),
			//new JSqueezeFilter(),
			new UglifyJs2Filter(),
			//new Yui\JsCompressorFilter($this->app['baseDir'].'/vendor/bin/yuicompressor.jar'),
		));
		
		return $js->dump();
		
	}
	
	private function compressCss($file){
		
		$css = new AssetCollection(array(
			new FileAsset($file),
		), array(
			new UglifyCssFilter(),
			//new Yui\CssCompressorFilter($this->app['baseDir'].'/vendor/bin/yuicompressor.jar'),
		));
		
		return $css->dump();
		
	}
	
}