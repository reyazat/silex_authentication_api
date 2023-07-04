<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class UserController


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
		$routing->get("/user/{userid}",[$this,'userController']);
		$routing->get("/user/ids/{usersids}",[$this,'usersidsController']);
		$routing->get("/user",[$this,'getAllUserController']);
		$routing->put("/user/{userid}",[$this,'updateUserController']);
		$routing->post("/user",[$this,'addUserController']);
		$routing->delete("/user/{userid}",[$this,'removeUserController']); 
		$routing->get("/user/device/{userid}",[$this,'getDeviceTokenByUserId']); 
		$routing->get("/user/device/ids/{userids}",[$this,'getDeviceTokenByUserIds']); 
		
		
        $routing->post("/user/picture/{userid}",[$this,'profilePictureUploadController']);
        $routing->delete("/user/picture/{userid}",[$this,'profilePictureRemoveController']);
        $routing->get("/user/picture/{userid}/{last_modify}",[$this,'getProfilePictureController']);
               

        

    }

    public function getAllUserController(){
		$params = $this->app['helper']('RequestParameter')->getParameter();
        $payLoad = $this->app['helper']('UserController_UserHp')->getAllUser($params);

        return setResponse($payLoad);
    }
	
    public function userController($userid){

        $payLoad = $this->app['helper']('UserController_UserHp')->getUserInfo($userid);
		return setResponse($payLoad);
		
    }
	
	public function usersidsController($usersids){

        $payLoad = $this->app['helper']('UserController_UserHp')->getUsersByIDs($usersids);
		return setResponse($payLoad);
		
    }
	public function addUserController(Request $request)
	{
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('UserController_UserHp')->addUserInfo($params);
		return setResponse($payLoad);
	}
	public function updateUserController($userid){
		
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('UserController_UserHp')->updateUserInfo($userid,$params);
		
		return setResponse($payLoad);
		
	}
	public function removeUserController($userid){
		
		$payLoad = $this->app['helper']('UserController_UserHp')->removeUser($userid);
		return setResponse($payLoad);
		
	}
	
	public function getDeviceTokenByUserId($userid){
       
        $payLoad = $this->app['helper']('DeviceController_DeviceHp')->getDeviceToken($userid);
		return setResponse($payLoad);
    }
	
	public function getDeviceTokenByUserIds($userids){
       
        $payLoad = $this->app['helper']('DeviceController_DeviceHp')->getDeviceTokenByIDS($userids);
		return setResponse($payLoad);
    }
	
	
    public function profilePictureRemoveController($userid)
    {
        $payLoad = $this->app['helper']('UserController_UserHp')->profilePictureRemove($userid);
        return setResponse($payLoad);
    }

    public function profilePictureUploadController($userid, Request $request)
    {
		$payLoad = [];
		if($request->files->has('userimage') && $this->app['helper']('Utility')->notEmpty($request->files->get('userimage')) ){
        $file_picture=$request->files->get('userimage');

        $payLoad = $this->app['helper']('UserController_UserHp')->profilePictureUpload($userid , $file_picture);
		}else{
			 $msg = $this->app['translator']->trans('FileHasNotBeenUploaded');
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}
			
        return setResponse($payLoad);
    }
    	
    


    public function getProfilePictureController($userid)
    {
        $filename = $this->app['helper']('UserController_UserHp')->getProfilePicture($userid);
		
		$file = new File($filename);

		$response = new Response();

		// Set headers
		$response->headers->set('Cache-Control', 'private');
		$response->headers->set('Content-type', $file->getMimeType());
		$response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getBaseName(). '";');
		$response->headers->set('Content-length', $file->getSize());

		// Send headers before outputting anything
		$response->sendHeaders();

		$response->setContent(file_get_contents($filename));

		return $response;
		
	}

	
    
}
