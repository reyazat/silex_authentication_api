<?php

namespace Helper\ForgetPasswordController;



class ForgetHp
{

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
	
	public function securityQuestions()
    {
        $payLoad = [
            'status' => 'Success',
            'message' => '',
			'code'	=> 200,
            'data' => $this->app['load']('Models_SecurityQuestionModel')->select('question')->inRandomOrder()->limit(7)->get()
        ];

        return $payLoad;
    }
	public function getMethods($credential , $username){
        if (!$this->app['helper']('Utility')->notEmpty($credential) || !$this->app['helper']('Utility')->notEmpty($username)) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name , Credential'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($payLoad['code'] === 200) {
				$count = 1;
				if($this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch($credential.'bluckcount'.$this->app['request_content']->getClientIp())))
					$count = $count + $this->app['cache']->fetch($credential.'bluckcount'.$this->app['request_content']->getClientIp());
					$this->app['cache']->store($credential.'bluckcount'.$this->app['request_content']->getClientIp(),$count,5400);
					
				if($count <= $this->app['config']['MaximumRequest'] ){
				
					$username = $this->app['helper']('Utility')->clearField($username);
					$payLoad = $this->app['load']('Models_UserModel')->findUserByEmail($username);
					
					if ($payLoad['code'] === 200) {
						$user['user_id'] = $payLoad['data']['user_id'];
						$user['user_type'] = $payLoad['data']['user_type'];
						$user['username'] = $payLoad['data']['username'];
						$user['firstname'] = $payLoad['data']['firstname'];
						$user['firstname'] = $payLoad['data']['firstname'];
						$user['lastname'] = $payLoad['data']['lastname'];
						$user['unique_token'] = $payLoad['data']['unique_token'];
						if ($this->app['helper']('Utility')->notEmpty($payLoad['data']['security_questions']) && $this->app['helper']('Utility')->isJSON($payLoad['data']['security_questions'])){
							$res = ['methods'=>array('e-mail', 'security_questions') , 'security_questions'=>$payLoad['data']['security_questions'],'data'=>$user];
							$result = ['methods'=>array('e-mail', 'security_questions')];
						}else{
							$res = ['methods'=>array('e-mail'),'data'=>$user];
							$result = ['methods'=>array('e-mail')];
						}
						$this->app['cache']->store('forgot_'.$username, $this->app['helper']('Utility')->encodeJson($res), 120);
						$this->app['cache']->delete($credential.'bluckcount'.$this->app['request_content']->getClientIp());
						$payLoad = ['status' => 'Success', 'message' => '', 'code'=>200, 'data' =>$result ];
						
					} 
				}else{
					$msg = $this->app['translator']->trans('MaximumRequestAttempt');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 429];
				}
			}
        }
        return $payLoad;
    }
	public function AddQuestionsByuserId($params){
        if (!$this->app['helper']('Utility')->notEmpty($params)) {
            $msg = $this->app['translator']->trans('422');
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 422];
        } else{
			$payLoad = $this->app['load']('Models_UserModel')->updateUserInfo($this->app['oauth']['id_user'], ['security_questions'=>$this->app['helper']('Utility')->encodeJson($params)]);
		}
        return $payLoad;
    }
	
	public function DeleteQuestionsByuserId(){
		
		$payLoad = $this->app['load']('Models_UserModel')->updateUserInfo($this->app['oauth']['id_user'], ['security_questions'=>'']);
		return $payLoad;
    }
	
	public function verifyMethod($credential , $params){
        if (!$this->app['helper']('Utility')->notEmpty($credential) || 
			!isset($params['username']) || 
			(isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
			!isset($params['method']) || 
			(isset($params['method']) && !$this->app['helper']('Utility')->notEmpty($params['method']))
			){
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name , Credential , Forget Method'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($payLoad['code'] === 200) {
				$username = $this->app['helper']('Utility')->clearField($params['username']);
				$method = $this->app['helper']('Utility')->clearField($params['method']);
				if($this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch('forgot_'.$username))){
					$getmethods = $this->app['helper']('Utility')->decodeJson($this->app['cache']->fetch('forgot_'.$username));
					if(in_array($method, $getmethods['methods'])){
						
						if($method == 'e-mail'){
							$email_data = [];
							$finder = new \Symfony\Component\Finder\Finder();
							$finder->name('Forgetpassword.twig')->depth('== 0');
							$finder->files()->in($this->app['baseDir'].'/Src/View/PublicController/');
							foreach ($finder as $file) {
								$email_data['content'] = $file->getContents();
							}
							$key = $this->app['helper']('CryptoGraphy')->randomPassword();
							$this->app['cache']->store('resetpass_'.$key,$this->app['helper']('Utility')->encodeJson($getmethods['data']), 86400);
							$email_data['to_variables'] = array('key'=>$key,'url' => $this->app['config']['webservice']['view'] . 'forget/email/' . $key, 'softwarename'=>$this->app['config']['software']['name'], 'emailsupport' => $this->app['config']['software']['global_email']);
            
							$email_data['to'] = $getmethods['data']['username'];
							$email_data['subject'] = 'Reset Your Password - '.$this->app['config']['software']['name'];
         
							$payLoad = $this->app['helper']('SendMail')->sendMessage($email_data);
							if ($payLoad['code'] === 200) {
								$msg = $this->app['translator']->trans('ResetMail');
								$payLoad = ['status' => 'Success', 'message' => $msg, 'code' => 200];
							}
			
						}elseif($method == 'security_questions'){
							$security_questions = $this->app['helper']('Utility')->decodeJson($getmethods['security_questions']);
							$this->app['cache']->store('userquestion_'.$getmethods['data']['user_id'],$this->app['helper']('Utility')->encodeJson(['security_questions'=>$security_questions,'data'=>$getmethods['data']]), 1800);
							$security_questions = array_keys($security_questions);
							
							$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200, 'data'=>['user_id'=>$getmethods['data']['user_id'],'security_questions'=>$security_questions]];
						}
						
					}else{
						$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User name , Forget Method'));
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 401];
					}
				}else{
					$msg = $this->app['translator']->trans('RecaptchaExpired');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 401];
				}				
			}
		}
		return $payLoad;
	}
		
	public function verifyEmailCode($credential , $key){
        if (!$this->app['helper']('Utility')->notEmpty($credential) || !$this->app['helper']('Utility')->notEmpty($key)){
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential , Code'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			$source = $payLoad['data']['source'];
			if ($payLoad['code'] === 200) {
				if($this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch('resetpass_'.$key))){
					$getUser = $this->app['helper']('Utility')->decodeJson($this->app['cache']->fetch('resetpass_'.$key));
					$this->app['cache']->delete('resetpass_'.$key);
					$getUser['source'] = $source;
					$getUser['cDate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
					$jwt = $this->app['helper']('JWTHp')->createToken($getUser);
					$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200, 'data'=>['token'=>$jwt]];
				}else{
					$msg = $this->app['translator']->trans('RecaptchaExpired');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 401];
				}	
			}
		}
		return $payLoad;
	}
		
		
	public function verifySecurityAnswer($credential , $params){
        if (!$this->app['helper']('Utility')->notEmpty($credential) || 
			!isset($params['answers']) || 
			(isset($params['answers']) && !$this->app['helper']('Utility')->notEmpty($params['answers'])) || 
			!isset($params['user_id']) || 
			(isset($params['user_id']) && !$this->app['helper']('Utility')->notEmpty($params['user_id'])) ){
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential , User ID , Answers'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			$source = $payLoad['data']['source'];
			if ($payLoad['code'] === 200) {
				$user_id = $this->app['helper']('Utility')->clearField($params['user_id']);
				$count = 1;
				if($this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch($credential.'blucked'.$this->app['request_content']->getClientIp())))
					$count = $count + $this->app['cache']->fetch($credential.'blucked'.$this->app['request_content']->getClientIp());
				if($count <= $this->app['config']['MaximumRequest'] ){
					$this->app['cache']->store($credential.'blucked'.$this->app['request_content']->getClientIp(),$count,5400);
					if($this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch('userquestion_'.$user_id))){
						$userquestion = $this->app['helper']('Utility')->decodeJson($this->app['cache']->fetch('userquestion_'.$user_id));
						$getquestions = array_keys($params['answers']);
						$userquestions = array_keys($userquestion['security_questions']);
						$access = true;
						foreach ($getquestions as $value) {
							if (!in_array($value, $userquestions)) {
								$access = false;
							}
						}
						if($access){
							if(count(array_intersect($userquestion['security_questions'], $params['answers'])) == count($userquestion['security_questions'])){
								$this->app['cache']->delete($credential.'blucked'.$this->app['request_content']->getClientIp());
								
								$getUser = $userquestion['data'];
								$getUser['source'] = $source;
								$getUser['cDate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
								$this->app['cache']->delete('userquestion_'.$user_id);
								$jwt = $this->app['helper']('JWTHp')->createToken($getUser);
								$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200, 'data'=>['token'=>$jwt]];
							}else{
								$msg = $this->app['translator']->trans('IncorrectAnswer');
								$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
							}
						}else{
							$msg = $this->app['translator']->trans('IncorrectAnswer');
							$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
						}
						
					}else{
						$msg = $this->app['translator']->trans('RecaptchaExpired');
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 401];
					}
				}else{
					$msg = $this->app['translator']->trans('MaximumRequestAttempt');
					$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 429];
				}
			}
		}
		return $payLoad;
	}
	
	public function changePassword($credential , $token , $params = []){
		 if (!$this->app['helper']('Utility')->notEmpty($credential) || 
			!$this->app['helper']('Utility')->notEmpty($token) || 
			!isset($params['password']) || 
			(isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password'])) || 
			!isset($params['confirm_password']) || 
			(isset($params['confirm_password']) && !$this->app['helper']('Utility')->notEmpty($params['confirm_password'])) ){
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Token ,Password , Confirm Password'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }else{
			$payLoad = $this->app['helper']('JWTHp')->verifyToken($token);
			if ($payLoad['status'] === 'Success') {
				$userId = $payLoad['data']['id_user'];
				$username = $payLoad['data']['username'];
				$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
				if ($payLoad['status'] === 'Success') {
					if(strlen($params['password']) < 8) {
						$msg = $this->app['translator']->trans('MinLength', array('%length%' => '8'));
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 406];
					}else if($params['password'] !== $params['confirm_password']) {
						$msg = $this->app['translator']->trans('EqualPass');
						$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 406];
					}else{
						$fields['password'] = $params['password'];
						$payLoad = $this->app['helper']('UserController_UserHp')->updateUserInfo($userId,$fields);
						if ($payLoad['status'] == 'Success') {
							
							$email_data = [];
							$finder = new \Symfony\Component\Finder\Finder();
							$finder->name('resetpassword.twig')->depth('== 0');
							$finder->files()->in($this->app['baseDir'].'/Src/View/PublicController/');
							foreach ($finder as $file) {
								$email_data['content'] = $file->getContents();
							}
							$name = explode('@',$username);
							$email_data['to_variables'] = array('name'=>$name[0], 'softwarename'=>$this->app['config']['software']['name'],'emailsupport' => $this->app['config']['software']['global_email']);
							$email_data['to'] = $username;
							$email_data['subject'] = 'Password change for your account - '.$this->app['config']['software']['name'];
							$this->app['helper']('SendMail')->sendMessage($email_data);
						}							
					}
				}
			}
		}
		return $payLoad;
	}
	
}
