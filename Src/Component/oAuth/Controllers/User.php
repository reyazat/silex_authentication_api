<?php

namespace Component\oAuth\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\oAuth\Helpers\CompanyHp;
use Component\oAuth\Helpers\UserHp;
//

use Component\oAuth\Models\SoftwareUser;
use Component\oAuth\Models\CompanyUser;
use Component\oAuth\Models\InviteUser;


class User implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];
		  
		
		$controllers->match(
            '/list',
			function(Request $request){
			
				$payLoad = [];
				$case = $request->get('action');
				switch($case){
						
					case'all' : 
						$idCompany = $request->get('id_company');
						$CompanyHp = new CompanyHp($this->app);
						$payLoad = $CompanyHp->companyAllUserList($idCompany);
					break;
						
					case'software' :
						$headers = $request->headers->all();
						$apiKey = '';
						
						if(isset($headers['www-authenticate'])){
							$apiKey = $headers['www-authenticate'][0];
						}
						$getParameter = $this->app['helper']('RequestParameter')->getParameter($request);
						$payLoad = $this->app['component']('oAuth_Helpers_UserHp')->softwareUsers($apiKey,$getParameter);
					break;
					
					default: 
						$idCompany = $request->get('id_company');
						$idUser = $request->get('id_user');
						$CompanyHp = new CompanyHp($this->app);
						$payLoad = $CompanyHp->companyUserList($idCompany,$idUser);
				}
				
				
				return $this->app->json($payLoad);
			
			}
			
        )->method('GET|POST');

		
		
		$controllers->post(
            '/invite',
			function(Request $request){
				
				$payLoad = [];
				$UserHp = new UserHp($this->app);
				$case = $request->get('action');
			
				if(isset($case) &&  $this->app['helper']('Utility')->notEmpty($case)){
					
					switch($case){
							
						case'add' :
							$payLoad = $UserHp->addInvite($request);
						break;
							
						case'checkcode' :
							$payLoad = $UserHp->checkInviteCode($request);
						break;
							
						case'list':
							$payLoad = $UserHp->inviteList($request);
						break;
							
						case'update':
							$payLoad = $UserHp->updateinviteList($request);
						break;
							
						case'remove':
							$payLoad = $UserHp->removeFromInviteList($request);
						break;
							
						case'cancel':
							$payLoad = $UserHp->cancelinvite($request);
						break;
							
						case'resend':
							$payLoad = $UserHp->resendinvite($request);
						break;
							
						default: $payLoad = ['status'=>'error','message'=>'Some require fields are empty.'];
							
					}
					
				}else{
					
					$payLoad = ['status'=>'error','message'=>'Some require fields are empty.'];
					
				}
			
				return $this->app->json($payLoad);
			
			}
			
        );
		
		$controllers->post(
			 '/info',
			 function(Request $request){
				
				$res = [];
				$SoftwareUser = new SoftwareUser($this->app);
				$case = $request->get('action');
				if(isset($case) &&  $this->app['helper']('Utility')->notEmpty($case)){
					
					switch($case){

						case'identify' :
							
							$idUser = $request->get('id_user');
							if(empty($idUser)){

								$res = $this->app['load']('Component_oAuth_Helpers_AuthenticateHp')->validateAccessToken($request);								
								$request->request->set('id_user', $res['user_id']);
								$idUser = $res['user_id'];
							}
							$res = $SoftwareUser->getUserByIdentify($idUser);
					
						break;

						case'identify&company' :
					
							$idUser = $request->get('id_user');
							$idCompany = $request->get('id_company');	
							$res = $SoftwareUser->getUserinfo($idUser,$idCompany);
							
						break;

						case'identify&password' :
					
							$password = $request->get('password');
							$id_user = $request->get('id_user');	
							$res = $SoftwareUser->getUserByPassword($password,$id_user);
							
						break;
							
						case'email' :
							$email = $request->get('email');
							$res = $SoftwareUser->checkDuplicateUser($email);
						break;

						case 'photo':
							$params = $this->app['helper']('RequestParameter')->postParameter();
							//$file = $request->get('file');
							//$file_size = $file->getClientSize();
							$res = ['success','file'=>$params];
							break;

						default: '';
							
					}
					
				}
			
				return $this->app->json($res);
			
			}
		);
		
		$controllers->post(
			 '/update',
			 function(Request $request){
		
				$idUser = $request->get('id_user');
				$SoftwareUser = new SoftwareUser($this->app);
				$payLoad = $SoftwareUser->editUser($idUser,$request);
				return $this->app->json($payLoad);
			
			}
		);
		
		$controllers->post(
			 '/company',
			 function(Request $request){
				 
				$CompanyHp = new CompanyHp($this->app);
				$case = $request->get('action');
				switch($case){
						
					case'addToCompany' :
						
						$payLoad = $CompanyHp->addUserToCompany($request);
						
					break;
						
					case'delete':
						
						$idEmployee = $request->get('id_employee');
						$idUser = $request->get('id_user');
						$idCompany = $request->get('id_company');

						$payLoad = $CompanyHp->deleteEmployeeFromCompany($idEmployee,$idUser,$idCompany);
						
					break;
						
					case'deleteCompany' :
						
						$payLoad = $CompanyHp->deleteCompany($request);
						
					break;
						
					case'edit_user_interface' :
						$payLoad = $CompanyHp->updateInterface($request);
					break;
						
					case'edit' :
						
						$payLoad = $CompanyHp->updateUserCompany($request);
					break;
					
					case'edit_option' :
						$params = $this->app['helper']('RequestParameter')->postParameter();
						$payLoad = $CompanyHp->updateUserOptions($params);
					break;
						
					case'list' :
						
						$idUser = $request->get('id_user');
						$payLoad = $CompanyHp->getlistOfCompany($idUser);
						
					break;
						
					case'editDetails':
						
						$payLoad = $CompanyHp->editCompanyDetails($request);
						
					break;
						
					case'child':
						
						$payLoad = $CompanyHp->userChild($request);
						
					break;
						
					case'groupingUser':
						$idCompany = $request->get('owner_company');
						$idUser = $request->get('id_user');
						$payLoad = $this->app['component']('oAuth_Helpers_CompanyHp')->groupingUser($idCompany,$idUser);
					break;
						
					case'count':
						
						$idCompany = $request->get('id_company');
						$CompanyUser = new CompanyUser($this->app);
						$countUser = $CompanyUser->countUser($idCompany);
						
						$InviteUser = new InviteUser($this->app);
						$countInvite = $InviteUser->inviteCount($idCompany);
						
						$payLoad = ['status'=>'success','count'=>($countUser['count']+$countInvite['count'])];
						
					break;
						
					default: 
						$payLoad = $CompanyHp->saveCompany($request);
					break;
						
				}
				
				return $this->app->json($payLoad);
			
			 }
		);
		
		$controllers->get(
			 '/live',
			 function(Request $request){
				 
				 $results = $this->app['component']('oAuth_Models_Lives')->tableList();
				 return $this->app->json($results);
				 
			 }
		);
		
		$controllers->get(
			 '/usage',
			 function(Request $request){
				 
				 $payLoad = [];
				 $action = $request->get('action');
				 if($this->app['helper']('Utility')->notEmpty($action)){
					 
					 switch($action){
						
						case'user' :
							 $filters = $request->get('filtering');
							 $decodeFilter = json_decode($filters,true);
							 $payLoad = $this->app['component']('oAuth_Models_UserUsage')->tableList($decodeFilter);
						break;
							 
						case'company' :
							 $params = $this->app['helper']('RequestParameter')->getParameter();
							 $payLoad = $this->app['component']('oAuth_Models_UserUsage')->tableListByCompany($params);
						break;
	 
						default:
							
							$msg = $this->app['translator']->trans('AccessDenied');
							$payLoad = ['status'=>'error','message'=>$msg];
							 
					 }
					 
				 }else{
					 
					$msg = $this->app['translator']->trans('AccessDenied');
					$payLoad = ['status'=>'error','message'=>$msg];
					 
				 }
				 
				 return $this->app->json($payLoad);
				 
			 }
		);
		
		$controllers->get(
			 '/device',
			 function(Request $request){
				 $idUser = $request->get('id_user');
				 $payLoad = $this->app['component']('oAuth_Models_DeviceToken')->getDevice($idUser);
				 
				 return $this->app->json($payLoad);
			 }
		);

        $controllers->post('/note', function(Request $request) {
            $postParams = $this->app['helper']('RequestParameter')->postParameter($request);
            return $this->app->json($this->app['component']('oAuth_Helpers_UserHp')->saveNote($postParams));
        });

        $controllers->post('/note/{id_user}', function(Request $request, $id_user) {
            $postParams = $this->app['helper']('RequestParameter')->postParameter($request);
            return $this->app->json($this->app['component']('oAuth_Helpers_UserHp')->saveNote($postParams, $id_user));
        })->assert('id_user', '.\_\d+');
		
		$controllers->get(
			 '/loginHistory',
			 function(Request $request){
				 
				$params = $this->app['helper']('RequestParameter')->getParameter();
				$payLoad = $this->app['component']('oAuth_Models_LoginIp')->loginHistory($params);
				 
				return $this->app->json($payLoad);
				 
			 }
		);
		
        return $controllers;
    }

	
}

