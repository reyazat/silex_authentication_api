<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;

class SubdomainController


{	/**
 * Application
 *
 * @var Silex\Application
 */
    protected $app;
    public function __construct($app)
    {
        $this->app = $app;
    }


    public function addRoutes($routing){
		$routing->get("/subdomain/verify/{subdomain}",[$this,'getSubdomainBySubdomain']);
		$routing->get("/subdomain/{user_id}",[$this,'getSubdomainByUderId']);
		$routing->get("/subdomain/code/{subdomain_id}",[$this,'getSubdomainById']);
		//$routing->get("/subdomain",[$this,'getAllSubdomain']);
		$routing->put("/subdomain/{subdomain_id}",[$this,'update']);
		//$routing->post("/subdomain",[$this,'add']);
		
		$routing->post("/subdomain/picture/{subdomain_id}",[$this,'pictureUploadController']);
        $routing->delete("/subdomain/picture/{subdomain_id}",[$this,'pictureRemoveController']);
        $routing->get("/subdomain/getpicture/{subdomain_id}/{size}/{last_modify}",[$this,'getPictureController']);
        $routing->get("/subdomain/getpicture/{subdomain_id}/{last_modify}",[$this,'getPictureController']);
    }
	
	
	public function getSubdomainBySubdomain($subdomain , Request $request)
	{
		$credential = $request->headers->get('credential');
		$payLoad = $this->app['helper']('Subdomain_SubdomainHp')->getSubdomainByName($credential , $subdomain);
		return setResponse($payLoad);
	}
	public function getSubdomainByUderId($user_id , Request $request)
	{	
		$payLoad = $this->app['helper']('Subdomain_SubdomainHp')->getSubdomainByUserId($user_id);
		return setResponse($payLoad);
	}
	
	public function getSubdomainById($subdomain_id , Request $request)
	{	$credential = $request->headers->get('credential');
	 
		$payLoad = $this->app['helper']('Subdomain_SubdomainHp')->getSubdomainById($credential,$subdomain_id);
		return setResponse($payLoad);
	}
	
	public function update($subdomain_id , Request $request)
	{	
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('Subdomain_SubdomainHp')->subdomainupdate($subdomain_id,$params);
		return setResponse($payLoad);
	}
	
	
	 public function pictureRemoveController($subdomain_id)
    {
        $payLoad = $this->app['helper']('Subdomain_SubdomainHp')->pictureRemove($subdomain_id);
        return setResponse($payLoad);
    }

    public function pictureUploadController($subdomain_id, Request $request)
    {
		$payLoad = [];
		if($request->files->has('logo') && $this->app['helper']('Utility')->notEmpty($request->files->get('logo')) ){
        $file_picture=$request->files->get('logo');

        $payLoad = $this->app['helper']('Subdomain_SubdomainHp')->pictureUpload($subdomain_id , $file_picture);
		}else{
			 $msg = $this->app['translator']->trans('FileHasNotBeenUploaded');
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}
			
        return setResponse($payLoad);
    }
    	
    


    public function getPictureController($subdomain_id,$size = 200)
    {
        $filename = $this->app['helper']('Subdomain_SubdomainHp')->getPicture($subdomain_id);
		$size = (int) $size;
		if($size < 10 ){$size = 200;}
		
		$image = new \Gumlet\ImageResize($filename);
		$image->resizeToHeight($size);
		return $image->getImageAsString(IMAGETYPE_PNG);
		
		
	}
	
	
}
