<?php
namespace Helper;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;

use Assetic\Filter\JpegoptimFilter;
use Assetic\Filter\JpegtranFilter;

use Assetic\Filter\PngoutFilter;
use Assetic\Filter\OptiPngFilter;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ImageOptimizer{
	
	protected $app;
	
	public function __construct($app){
		
		$this->app = $app; 
		
	}
	
	public function optimizer($image){
		
		$imagePath = '';
		$imagePath = $this->app['baseDir'] . '/Web/images/'.$image;
		
		// get name of file
		$imgName = '';
		$imgName = $this->app['helper']('Utility')->getName($imagePath);
		
		// get extension of image
		$type = '';
		$type = $this->app['helper']('Utility')->getExt($imgName);
		
		// check if Cache Dir exist in Web folder in Root Dir
		$fs = new Filesystem();
		$checkFile = $fs->exists($this->app['baseDir'] . '/Web/Cache/Images');

		// if Cache Dir not Exist in Web Dir in Root, make it
		if($checkFile === false){
			try {
				$fs->mkdir($this->app['baseDir'] . '/Web/Cache/Images');
			} catch (IOExceptionInterface $e) {

				echo "An error occurred while creating your directory at ".$e->getPath();
			}
		}
		
		// get last time update of file
		$lastModify = $this->app['helper']('Utility')->lastModify($imagePath);
		
		$optImagePath = $optImagePathWithOutVersion = $optImageUrl = '';
		
		$optImagePathWithOutVersion = $this->app['baseDir'] . '/Web/Cache/Images/'.$image;
		$optImagePath = $optImagePathWithOutVersion.'?v='.$lastModify;
		$optImageUrl = $this->app['baseUrl'] . 'Cache/Images/'.$image.'?v='.$lastModify;

		// check optimize image exist or not
		$checkOptImgExist = $fs->exists($optImagePath);
		
		$imgLink = '';
		if($type == 'jpeg'){
			
			if($checkOptImgExist){
				$imgLink = self::makeImgLink($optImageUrl);
			}else{
				
				$imgContent = '';
				$imgContent = self::optimizeImgJpg($imagePath);
				self::saveOptImage($optImagePath,$optImagePathWithOutVersion,$imgContent);
				
				$imgLink = self::makeImgLink($optImageUrl);
				
			}
			
		}elseif ($type == 'png'){
			
			if($checkOptImgExist){
				$imgLink = self::makeImgLink($optImageUrl);
			}else{
				
				$imgContent = '';
				$imgContent = self::optimizeImgPng($imagePath);
				
				self::saveOptImage($optImagePath,$optImagePathWithOutVersion,$imgContent);
				
				$imgLink = self::makeImgLink($optImageUrl);
				
			}
			
		}else{
			
			if($checkOptImgExist){
				$imgLink = self::makeImgLink($optImageUrl);
			}else{
				
				$fs->copy($imagePath, $optImagePath);
				$fs->copy($imagePath, $optImagePathWithOutVersion);
				$imgLink = self::makeImgLink($optImageUrl);
			}
			
			
		}
		
		return $imgLink;
		
	}
	
	private function optimizeImgJpg($imgFile){
		
		$img = new AssetCollection(array(
			new FileAsset($imgFile),
		), array(
			new JpegtranFilter(),
			//new JpegoptimFilter(),
		));
		
		return $img->dump();
		
	}
	
	private function optimizeImgPng($imgFile){
		
		$img = new AssetCollection(array(
			new GlobAsset($imgFile),
		), array(
			new OptiPngFilter(),
			//new PngoutFilter($this->app['baseDir'].'/App/Resources/pngout/x86_64/pngout'),
		));
		
		return $img->dump();
		
	}
	
	private function saveOptImage($optImgPath,$optImgPathWithouVersion,$content){
		
		$fs = new Filesystem();
		$fs->dumpFile($optImgPath, '');
		$fs->dumpFile($optImgPathWithouVersion, $content);
		
		return true;
		
	}
	
	private function makeImgLink($imgUrl){
		
		return $imgUrl;
		
	}
	
}