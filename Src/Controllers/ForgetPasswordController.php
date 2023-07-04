<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ForgetPasswordController
{	protected $app;
	public function __construct($app)
	{
		$this->app = $app;
	}

	public function addRoutes($routing)
	{
		$routing->get("/security/questions", [$this, 'securityQuestionsController']);
		$routing->post("/security/questions", [$this, 'AddQuestionsController']);
		$routing->put("/security/questions", [$this, 'UpdateQuestionsController']);
		$routing->delete("/security/questions", [$this, 'DeleteQuestionsController']);
		
		$routing->get("/forget/methods/{username}", [$this, 'checkForgetMethodsController']);
		$routing->post("/forget/verifymethod", [$this, 'verifyMethodController']);
		$routing->get("/forget/email/{key}", [$this, 'verifyEmailCodeController']);
		$routing->post("/forget/answers", [$this, 'verifySecurityAnswerController']);
		
		$routing->put("/forget/resetpassword", [$this, 'changePasswordController']);
		

	}
	
	public function securityQuestionsController()
	{
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->securityQuestions();
		return setResponse($payLoad);
	}
	
	public function AddQuestionsController()
	{
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->AddQuestionsByuserId($params);
		return setResponse($payLoad);
	}
	public function UpdateQuestionsController()
	{
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->AddQuestionsByuserId($params);
		return setResponse($payLoad);
	}
	public function DeleteQuestionsController()
	{
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->DeleteQuestionsByuserId();
		return setResponse($payLoad);
	}
		
	public function checkForgetMethodsController(Request $request , $username)
	{	$credential = $request->headers->get('credential');
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->getMethods($credential , $username);
		return setResponse($payLoad);
	}
	public function verifyMethodController(Request $request)
	{	$credential = $request->headers->get('credential');
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->verifyMethod($credential , $params);
		return setResponse($payLoad);
	}
	public function verifyEmailCodeController(Request $request, $key)
	{	$credential = $request->headers->get('credential');
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->verifyEmailCode($credential , $key);
		return setResponse($payLoad);
	}
	public function verifySecurityAnswerController(Request $request)
	{	$credential = $request->headers->get('credential');
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->verifySecurityAnswer($credential , $params);
		return setResponse($payLoad);
	}
	public function changePasswordController(Request $request)
	{	$credential = $request->headers->get('credential');
		$token = $request->headers->get('token');
		$params = $this->app['helper']('RequestParameter')->postParameter();
		$payLoad = $this->app['helper']('ForgetPasswordController_ForgetHp')->changePassword($credential , $token , $params);
		return setResponse($payLoad);
	}

}
