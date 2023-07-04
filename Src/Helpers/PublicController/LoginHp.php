<?php

namespace Helper\PublicController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Silex\Provider\CsrfServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class LoginHp
{

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function signUp($credential = '' , $params = [])
    {	
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) || 
            !isset($params['username']) ||
            (isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
            !isset($params['password']) ||
            (isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password'])) ||
            !isset($params['device_token']) ||
            (isset($params['device_token']) && !$this->app['helper']('Utility')->notEmpty($params['device_token'])) ||
            !isset($params['device_type']) ||
            (isset($params['device_type']) && !$this->app['helper']('Utility')->notEmpty($params['device_type']))

        ) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Username, Password,Devcie Token, Device Type'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else if (isset($params['username']) && !$this->app['helper']('Utility')->isEmail($params['username'])) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Email'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			 // get login source
			$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($getLoginSource['status'] === 'Success') {
				unset($params['roles']);
				unset($params['id_pack']);
				unset($params['due_date']);
				unset($params['user_type']);
				file_put_contents('222.txt', serialize($params));
				$payLoad = $this->app['load']('Models_UserModel')->signUp($params);
				if ($payLoad['status'] == 'Success') {
					$params['user_id'] = $payLoad['data']['user_id'];
						
					$add_device = $this->app['load']('Models_DeviceTokenModel')->addDeviceToken($params);
					if ($add_device['status'] == 'Success') {
						$this->verificationEmail($params);
					}
				}
			}else { // error in get source
				$payLoad = $getLoginSource;
			}
        }

        return $payLoad;
    }

    public function verificationEmail($params = [])
    {
        $payLoad = [];
        $username = $this->app['helper']('Utility')->clearField($params['username']);
        $findUser = $this->app['load']('Models_UserModel')->findUserByEmail($username);
        if ($findUser['status'] === 'Success') {
            $key = $findUser['data']['unique_token'];
            $email_data = [];
			$finder = new \Symfony\Component\Finder\Finder();
			$finder->name('VerificationEmail.twig')->depth('== 0');
			$finder->files()->in($this->app['baseDir'].'/Src/View/PublicController/');
			foreach ($finder as $file) {
				$email_data['content'] = $file->getContents();
			}
			$email_data['to_variables'] = array('url' => $this->app['baseUrl'] . 'signup/verify/' . $key,'emailsupport' => $this->app['config']['software']['global_email']);
            
            $email_data['to'] = $username;
            $email_data['subject'] = 'Verification Email - '.$this->app['config']['software']['name'];
         
            $payLoad = $this->app['helper']('SendMail')->sendMessage($email_data);
            
        } else {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }

        return $payLoad;
    }

	
    public function signIn($credential = '', $params = [])
    {

        $payLoad = [];
		if (
            !$this->app['helper']('Utility')->notEmpty($credential) || 
            !isset($params['device_token']) ||
            (isset($params['device_token']) && !$this->app['helper']('Utility')->notEmpty($params['device_token'])) ||
            !isset($params['device_type']) ||
            (isset($params['device_type']) && !$this->app['helper']('Utility')->notEmpty($params['device_type']))

        ) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Devcie Token, Device Type'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {

            if (
                !isset($params['username']) ||
				(isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
				!isset($params['password']) ||
				(isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password']))
            ) {

                $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username, Password'));
                $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
            } else {

                $userName = $this->app['helper']('Utility')->clearField($params['username']);
                $pass = $this->app['helper']('Utility')->clearField($params['password']);

                $findUser = $this->app['load']('Models_UserModel')->findUserByEmail($userName);
                if ($findUser['status'] === 'Success') {

                    $verifyPass = $this->app['helper']('CryptoGraphy')->verifyPassword($pass, $findUser['data']['password']);
                    if ($verifyPass) { // password match

                        $userData = $findUser['data'];

                        // get login source
                        $getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
                        if ($getLoginSource['status'] === 'Success') {

							$params['user_id'] = $userData['user_id'];
							$add_device = $this->app['load']('Models_DeviceTokenModel')->addDeviceToken($params);
							$userData['source'] = $getLoginSource['data']['source'];
                            $jwt = $this->app['helper']('JWTHp')->createToken($userData);
	
                            $cacheId = $this->app['helper']('CryptoGraphy')->urlsafe_b64encode($userData['user_id'] . '-' . $credential);

                            $this->app['cache']->store($cacheId, $jwt);
                           
                            $payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' => ['token' => $jwt, 'user_id' => $userData['user_id'], 'user_type' => $userData['user_type']]];
                        } else { // error in get login source
                            $payLoad = $getLoginSource;
                        }
                    } else { // password not match

                        $msg = $this->app['translator']->trans('WrongLogin');
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
                    }
                } else {
                    $msg = $this->app['translator']->trans('WrongLogin');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
                }
            }
        }

        return $payLoad;
    }

     

    public function verifyToken($credential = '', $token = '')
    {

        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) ||            
            !$this->app['helper']('Utility')->notEmpty($token) 
			) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Token'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {

            $checkAccess = $this->app['helper']('JWTHp')->verifyToken($token);

            if ($checkAccess['status'] === 'Success') {
                $payLoad = (['status' => 'Success', 'message' => '', 'code'=>200, 'data' => $checkAccess['data']]);
            } else {
                $payLoad = $checkAccess;
            }
        }

        return $payLoad;
    }

    public function verifyEmail($key = '')
    {
        if (!isset($key) || (isset($key) && !$this->app['helper']('Utility')->notEmpty($key))) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Token'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
            $payLoad = $this->app['load']('Models_UserModel')->findUserByToken($key);
           
            if ($payLoad['status'] == 'Success') {
               
                $params['verified'] = 1;
                $payLoad = $this->app['load']('Models_UserModel')->updateUserInfo($payLoad['data']['user_id'], $params);
                $msg = $this->app['translator']->trans('Verification');
                if ($payLoad['status'] == 'Success')
					$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];
               
            }
        }
        return $payLoad;
    }

	public function addInvite($params){
		$payLoad = [];
        if (!isset($params['username']) ||
            (isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
			 !isset($params['user_type']) ||
            (isset($params['user_type']) && !$this->app['helper']('Utility')->notEmpty($params['user_type']))
			){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username ,UserType'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }else{
			
			if($this->app['helper']('Utility')->isEmail($params['username'])){

					$payLoad = $this->app['load']('Models_InviteModel')->addInvite($params);
					if ($payLoad['code'] === 200) {
						$this->invitationEmail($params);
					}				
			}else{
				
				$msg = $this->app['translator']->trans('InvalidEmail');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];			
				
			}
			
		}
		return 	$payLoad;
		
		
	}
	public function getAllInvite($params){
		$payLoad = $this->app['load']('Models_InviteModel')->getAllInvite($params);
		$payLoad['pagination']['usertype_list'] = $this->app['config']['parameters']['usertype_list'];
		$payLoad['pagination']['invite_status'] = ['Active', 'Used', 'Inactive'];
		$payLoad['pagination']['invite_status_color'] = ['Active'=>'#000', 'Used'=>'#000', 'Inactive'=>'#000'];
		
        return $payLoad;
	}
	
	public function updateInvite($inviteid, $params = [])
    {

        $payLoad = [];
        if ((isset($params['user_type']) && !$this->app['helper']('Utility')->notEmpty($params['user_type'])) ||
            (isset($params['status']) && !$this->app['helper']('Utility')->notEmpty($params['status'])) ||
            !$this->app['helper']('Utility')->notEmpty($inviteid)
        ) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Invite Id, User Type, Status'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$inviteid = $this->app['helper']('Utility')->clearField($inviteid);
            $payLoad = $this->app['load']('Models_InviteModel')->updateInvite($inviteid, $params);
        }

        return $payLoad;
    }
	
	public function removeInvite($inviteid)
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($inviteid)) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Invite Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$inviteid = $this->app['helper']('Utility')->clearField($inviteid);
            $payLoad = $this->app['load']('Models_InviteModel')->removeInvite($inviteid);
        }

        return $payLoad;
    }
	
	public function reSendInvite($inviteid)
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($inviteid)) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Invite Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$inviteid = $this->app['helper']('Utility')->clearField($inviteid);
			$payLoad = $this->app['load']('Models_InviteModel')->findById($inviteid);
			if($payLoad['status']=='Success'){
				if(!$this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch('inviteMail_'.$payLoad['data']['username']))){
					
					$this->invitationEmail($payLoad['data']);
					
				}else{
					$msg = $this->app['translator']->trans('Failedresend',array('%time%'=>30));
					$payLoad = ['status'=>'Error','message'=>$msg,'code' => 409];
				}
			}
        }
        return $payLoad;
    }
	
	public function verifyInviteCode($credential, $params)
    {
      $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) || 
            !isset($params['invitecode']) ||
            (isset($params['invitecode']) && !$this->app['helper']('Utility')->notEmpty($params['invitecode']))
        ) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Invite Code'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }else{
			 // get login source
			$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($getLoginSource['status'] === 'Success') {
				$params['invitecode'] = $this->app['helper']('Utility')->clearField($params['invitecode']);
				$payLoad = $this->app['load']('Models_InviteModel')->findByCode($params['invitecode']);
				
				if($payLoad['status']=='Success'){
					switch($payLoad['data']['status']){
						case'Active' :
							$payLoad = $payLoad;
						break;
						case'Used' :
							 $msg = $this->app['translator']->trans('Unableinvitation');
							$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 417];
						break;
						case'Inactive' :
							$msg = $this->app['translator']->trans('Unableinvitation');
							$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 417];
						break;
						default: 
							$msg = $this->app['translator']->trans('AccessDenied');
							$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 406];
					}
				}
			}else { // error in get source
				$payLoad = $getLoginSource;
			}
        }

        return $payLoad;
    }
	
	public function invitationEmail($params = [])
    {
        $payLoad = [];
        $username = $this->app['helper']('Utility')->clearField($params['username']);
        $findUser = $this->app['load']('Models_InviteModel')->findUserByEmail($username);
		
        if ($findUser['status'] === 'Success') {
            $key = $findUser['data']['unique_code'];
            $email_data = [];
			$finder = new \Symfony\Component\Finder\Finder();
			$finder->name('InvitationEmail.twig')->depth('== 0');
			$finder->files()->in($this->app['baseDir'].'/Src/View/PublicController/');
			foreach ($finder as $file) {
				$email_data['content'] = $file->getContents();
			}
			$email_data['to_variables'] = array('key'=>$key, 'url' => $this->app['baseUrl'] . 'verifyinvitecode?invitecode=' . $key,'emailsupport' => $this->app['config']['software']['global_email']);
            
            $email_data['to'] = $username;
            $email_data['subject'] = 'You have been invited to join '.$this->app['config']['software']['name'];
         
            $payLoad = $this->app['helper']('SendMail')->sendMessage($email_data);
            
        } else {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }

        return $payLoad;
    }


}
