<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;


class PublicController implements ControllerProviderInterface 
	
{	/**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){
		$this->app = $app;
				
		$index = $app['controllers_factory'];
		
		$index->get("/readme", [$this, 'readme'])->bind('readme');
		$index->post("/signin", [$this, 'signinController']);
		$index->post("/signup", [$this, 'signupController']);
		$index->get("/verify", [$this, 'verifyTokenController']);
		$index->get("/signup/verify/{key}", [$this, 'verifyEmailController']);
		
		$index->post("/invite", [$this, 'addInviteController']);
		$index->get("/invite", [$this, 'getAllInviteController']);
		$index->get("/invite/resend/{inviteid}", [$this, 'reSendInviteController']);
		$index->put("/invite/{inviteid}", [$this, 'updateInviteController']);
		$index->delete("/invite/{inviteid}", [$this, 'delInviteController']);
		
		$index->post("/verifyinvitecode", [$this, 'verifyInviteCodeController']);
		
		// DeviceController controller
		$deviceController = new DeviceController($this->app);
		$deviceController->addRoutes($index);
		
		// ForgetPassword controller
		$forgetPasswordController = new ForgetPasswordController($this->app);
		$forgetPasswordController->addRoutes($index);
		
		// User controller
		$userController = new UserController($this->app);
		$userController->addRoutes($index);
		
		return $index;
	}	
	public function readme(Request $request){
		
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->name('README.md')->depth('== 0');
		$finder->files()->in($this->app['baseDir']);
		foreach ($finder as $file) {
			$contents = $file->getContents();
		}
		$Parsedown = new \Parsedown();
		$contents = $Parsedown->setBreaksEnabled(true)->setMarkupEscaped(true)->setUrlsLinked(true)->text($contents , true); 					
		return '<link href="'.$this->app['baseUrl'].'Css/readme.css" rel="stylesheet" type="text/css">'.$contents ;
    }
	
	public function verifyEmailController($key = '')
	{
		$payLoad = $this->app['helper']('PublicController_LoginHp')->verifyEmail($key);
		return setResponse($payLoad);
	}
	public function verifyTokenController(Request $request)
	{
		$credential = $request->headers->get('credential');
		$token = $request->headers->get('token');
			
		$payLoad = $this->app['helper']('PublicController_LoginHp')->verifyToken($credential, $token);
		return setResponse($payLoad);
		
	}

	public function signinController(Request $request)
	{

		$credential = $request->headers->get('credential');
		
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->signIn($credential, $params);

		return setResponse($payLoad);
	}

	public function signupController(Request $request)
	{
		$credential = $request->headers->get('credential');
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->signUp($credential, $params);
		return setResponse($payLoad);
	}
	
	public function addInviteController(Request $request)
	{
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->addInvite($params);
		return setResponse($payLoad);
	}
	
	public function getAllInviteController()
	{
		$params = $this->app['helper']('RequestParameter')->getParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->getAllInvite($params);
		return setResponse($payLoad);
	}
	
	public function reSendInviteController($inviteid)
	{
		$payLoad = $this->app['helper']('PublicController_LoginHp')->reSendInvite($inviteid);
		return setResponse($payLoad);
	}
	public function updateInviteController($inviteid)
	{
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->updateInvite($inviteid, $params);
		return setResponse($payLoad);
	}
	
	public function delInviteController($inviteid)
	{
		$payLoad = $this->app['helper']('PublicController_LoginHp')->removeInvite($inviteid);
		return setResponse($payLoad);
	}
	
	public function verifyInviteCodeController(Request $request)
	{
		$credential = $request->headers->get('credential');
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('PublicController_LoginHp')->verifyInviteCode($credential, $params);
		return setResponse($payLoad);
	}
	
	
	
	
}

