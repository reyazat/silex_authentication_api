<?php
namespace Component\oAuth\Helpers;

use Component\oAuth\Models\SoftwareUser;
use Component\oAuth\Models\ForgetPass;

class ForgetPassHp{
	protected $app;
	protected $SoftwareUser;
	protected $ForgetPass;
	
	public function __construct($app){
		$this->app = $app;
		$this->SoftwareUser = new SoftwareUser($this->app);
		$this->ForgetPass = new ForgetPass($this->app);
    }
	private function decodeCode($code,$email){
		
		$userIdentity = '';
		
		$decodeCode = $this->app['helper']('CryptoGraphy')->md5decrypt($code);
		$decodeCode = str_replace($email,'',$decodeCode);
		$userIdentity = substr($decodeCode, 0, -14);
		
		return $userIdentity;
		
	}
	
	public function sendForgetPassMail($idUser,$code){
		
		$payLoad = [];
		
		if(!$this->app['helper']('Utility')->notEmpty($idUser) || 
		  !$this->app['helper']('Utility')->notEmpty($code)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id User,Code).'];
			
		}else{
			
			$userInfo = $this->SoftwareUser->getUserByIdentify($idUser);
			if($this->app['helper']('Utility')->notEmpty($userInfo)){

				$userIdentity = $this->decodeCode($code,$userInfo['email']);
		
				if($idUser == $userIdentity){
					
					$uniqueCode = $this->app['helper']('CryptoGraphy')->randomPassword(54);

					$payLoad = $this->ForgetPass->addForgetPass(['email'=>$userInfo['email'],
														  'code'=>$uniqueCode,
														  'cdate'=>$this->app['helper']('DateTimeFunc')->nowDateTime()]);
					
					if(isset($payLoad['status']) && $payLoad['status'] == 'success'){
						
						
						$content = $this->app['twig']->render('Elements/EmailTheme/ForgetPass.phtml',
															  ['user_name'=>$userInfo['email'],
															   'code'=>$uniqueCode]);


						$sendForgetPassMail = $this->app['helper']('OutgoingRequest')->postRequest('https://mailg.smartysoftware.net/v1/mailer/sendingrequests',
									['apiKey'=>'1c987c44-25bc-11e8-93eb-080027e61842'],
									['recipients'=>[$userInfo['email']],
									 'fromEmail'=>$this->app['config']['software']['global_email'],
									 'fromName'=>$this->app['config']['software']['name'],
									 'subject'=>'How to reset your '.$this->app['config']['software']['name'].' password.',
									 'html'=>$content,
									 'sendDate'=>$this->app['helper']('DateTimeFunc')->nowDateTime(),
									 'timeZone'=>'UTC',
									 'senderDomain'=>'mailg.smartysoftware.net']);
					
						if($sendForgetPassMail == true){
							
							$payLoad = ['status'=>'success','message'=>'email sent successfully.'];
							
						}else{
							$this->app['monolog.debug']->error('Forget password email not sent.');
							$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
							
							$payLoad = ['status'=>'error','message'=>$msg];
							
						}
						
						
					}else{
						$this->app['monolog.debug']->error('add Forget password failed.' ,$payLoad);
						$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
							
						$payLoad = ['status'=>'error','message'=>$msg];
						
					}

					
				}else{
					
					$this->app['monolog.debug']->warning('id users not matched.',['id user'=>$idUser,
																			'code'=>$code,
																			'id user from code'=>$userIdentity]);
					$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
					
					$payLoad = ['status'=>'error','message'=>$msg];
					
				}
				
			}else{
				
				$payLoad = ['status'=>'error','message'=>'Sorry!User with presented details not exist.'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function checkRequest($code){
		
		$findCode = $this->ForgetPass->codeInfo($code);
		if($this->app['helper']('Utility')->notEmpty($findCode)){
			
			$userIdentity = $this->SoftwareUser->checkDuplicateUser($findCode['email']);
			
			$code = $userIdentity.$this->app['helper']('DateTimeFunc')->nowDateTime('YdmsiH').$findCode['email'];
			$encodeCode = $this->app['helper']('CryptoGraphy')->md5encrypt($code);
			
			$payLoad = ['status'=>'success','code'=>$encodeCode,'id_user'=>$userIdentity,'email'=>$findCode['email']];
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'There is no forgot password request with presented details.'];
			
		}
		
		return $payLoad;
		
	}
	
}