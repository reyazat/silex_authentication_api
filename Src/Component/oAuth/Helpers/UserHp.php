<?php

namespace Component\oAuth\Helpers;

use Component\oAuth\Models\SoftwareUser;

use Component\oAuth\Models\InviteUser;


class UserHp{
	
	protected $app;
	protected $SoftwareUser;
	protected $InviteUser;
	
	public function __construct($app){
		$this->app = $app;
		$this->SoftwareUser = new SoftwareUser($this->app);
		$this->InviteUser = new InviteUser($this->app);
    }
	
	public function addInvite($request){
		
		$payLoad = [];
		$uniqueCode = $this->app['helper']('CryptoGraphy')->randomPassword(45);
		
		$details = [];
		$details['unique_code'] = $uniqueCode;
		$details['user_type'] = $request->get('user_type');
		$details['email'] = $request->get('email');
		$details['id_company'] = $request->get('id_company');
		$details['id_maker'] = $request->get('id_maker');
		$details['status'] = $request->get('status');
		$details['id_role'] = $request->get('id_role');
		
		if(!$this->app['helper']('Utility')->notEmpty($details['email']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['id_company']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['id_maker']) ||
		  !$this->app['helper']('Utility')->notEmpty($details['user_type'])){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Email,Id Company,Id Maker,User Type'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			if($this->app['helper']('Utility')->isEmail($details['email'])){
				
				$checkStatus = self::inviteMailQueue($details['id_company'].'-'.$details['email']); 
				if($checkStatus === true){
				
					$addInvite = $this->InviteUser->addInvite($details);
					if(isset($addInvite['status']) && $addInvite['status'] == 'error'){

						$payLoad = $addInvite;
						/*$this->app['monolog.debug']->error("error occurred in save invite email",$details);
						$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
						$payLoad = ['status'=>'error','message'=>$msg];*/

					}else{

						$sendEmail = self::sendInviteEmail($details['id_maker'],$details['id_company'],$addInvite['code'],$details['email']);

						if(isset($sendEmail['status']) && $sendEmail['status'] == 'success'){

							$this->InviteUser->updateInvite($addInvite['id'],['tracking_id'=>$sendEmail['trackingId']]);

							$msg = $this->app['translator']->trans('add', array('%name%' => 'Invite'));
							$payLoad = ['status'=>'success','message'=>$msg];

						}else{

							$this->app['monolog.debug']->error("error occurred in send invite email",$details);
							$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
							$payLoad = ['status'=>'error','message'=>$msg];

						}

					}
				}else{
					$payLoad = ['status'=>'error','message'=>'Failed to re-send invitation. Please wait 30 minutes and then try again.'];
				}
			}else{
				
				$msg = $this->app['translator']->trans('InvalidEmail', array());
				$payLoad = ['status'=>'error','message'=>$msg];
				
			}
			
		}
		return 	$payLoad;
		
		
	}
	
	public function checkInviteCode($request){
		
		$code = $request->get('invite_code');
		$res = $this->InviteUser->checkCode($code);

		$payLoad = [];
		
		switch($res['mood']){
				
			case'Active' :
				
				$payLoad = ['status'=>'success','data'=>$res];
				
			break;
				
			case'Used' :
				
				$payLoad = ['status'=>'error','message'=>'Sorry! this invitation used before.'];
				
			break;
				
			case'Canceld' :
				
				$payLoad = ['status'=>'error','message'=>'Sorry! '.$res['company_name'].' canceled invitation.'];
				
			break;
				
			default: $payLoad = ['status'=>'error','message'=>'Access Denied!','status_code'=>405];
				
		}
		
		return $payLoad;
		
	}
	
	public function removeFromInviteList($request){
		
		$idCompany = $request->get('id_company');
		$email = $request->get('email');
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) || 
		   !$this->app['helper']('Utility')->notEmpty($email)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty.'];
			
		}else{
			
			$payLoad = $this->InviteUser->removeFromInviteList($idCompany,$email);
			
		}
		
	}
	
	public function inviteList($request){
		
		$payLoad = [];
		$idCompany = $request->get('id_company');
		$idUser = $request->get('id_user');
		
		if($this->app['helper']('Utility')->notEmpty($idCompany) && 
		  $this->app['helper']('Utility')->notEmpty($idUser)){
			
			$userDetails = $this->SoftwareUser->getUserinfo($idUser,$idCompany);
			$payLoad = $this->InviteUser->inviteList($idCompany,$idUser,$userDetails['user_type']);
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'Sorry!Some required fields are empty(Id Company,Id User).'];
			
		}
		
		return $payLoad;
	}
	
	public function updateinviteList($request){
		
		$payLoad = [];
		
		$code = $request->get('code');
		$data = $request->get('data');
		
		if($this->app['helper']('Utility')->notEmpty($code) && $this->app['helper']('Utility')->notEmpty($data)){
			
			$res = $this->InviteUser->checkCode($code);
			$payLoad = $this->InviteUser->updateInvite($res['id'],$data);
			
		}else{
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Code).'];
			
		}
		
		return $payLoad;
		
	}
	
	public function cancelinvite($request){
		
		$idCompany = $request->get('id_company');
		$idInvite = $request->get('id_invite');
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) ||
		  !$this->app['helper']('Utility')->notEmpty($idInvite)){
			
			$payLoad = ['status'=>'error','message'=>'Some require fields are empty(Id Company,Id Invite).'];
			
		}else{
			
			$inviteDetails = $this->InviteUser->getInvite($idInvite);
			if($this->app['helper']('Utility')->notEmpty($inviteDetails)){
				
				if($inviteDetails['id_company'] == $idCompany){
					
					$payLoad = $this->InviteUser->updateInvite($idInvite,['mood'=>'Canceld']);
					
				}else{
					
					$this->app['monolog.debug']->error('id company and inviter company not equal',['id invite'=>$idInvite,
																							  'id company'=>$idCompany]);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
					
				}
				
			}else{
				
				$this->app['monolog.debug']->error('id invite not exist',['id invite'=>$idInvite]);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}
			
			
		}
		
		return $payLoad;
		
	}
	
	private function sendInviteEmail($idMaker,$idCompany,$code,$email){
		
		$makerDetails = $this->SoftwareUser->getUserinfo($idMaker,$idCompany);
		if($this->app['helper']('Utility')->notEmpty($makerDetails['first_name']) || $this->app['helper']('Utility')->notEmpty($makerDetails['last_name'])){
			
			$makerDetails['username'] = $makerDetails['first_name'].' '.$makerDetails['last_name'];
			
		}else{
			
			$makerDetails['username'] = $makerDetails['email'];
			
		}

		$content = $this->app['twig']->render('Elements/EmailTheme/Invite.phtml',
											  ['company_name'=>$makerDetails['company_name'],
											   'maker_name'=>$makerDetails['username'],
											   'maker_email'=>$makerDetails['email'],
											   'code'=>$code]);

		$payLoad = $this->app['helper']('OutgoingRequest')->postRequest('https://mailg.smartysoftware.net/v1/mailer/sendingrequests',
			['apiKey'=>'1c987c44-25bc-11e8-93eb-080027e61842'],
			['recipients'=>[$email],
			 'fromEmail'=>$makerDetails['email'],
			 'fromName'=>$makerDetails['username'],
			 'subject'=>'Invite to be user of '.$makerDetails['company_name'],
			 'html'=>$content,
			 'sendDate'=>$this->app['helper']('DateTimeFunc')->nowDateTime(),
			 'timeZone'=>'UTC',
			 'senderDomain'=>'smartymailcrm.net']);

		
		return $payLoad;
		
	}
	
	private function inviteMailQueue($idInvite){
		
		$res = false;
		
		$invites = [];
		$seassionExist = $this->app['predis']['cache']->exists('inviteUsers');
		if($seassionExist){
			
			$getInvites = $this->app['predis']['cache']->get('inviteUsers');
			$invites = unserialize($getInvites);
			
			$findId = array_search($idInvite, array_column($invites, 'id'));
			if($findId === false){ // id invite not exist
				
				$res = true;
				$invites[] = ['id'=>$idInvite,
							  'cnt'=>1,
							  'cDate'=>$this->app['helper']('DateTimeFunc')->nowDateTime()];
				
			}else{ // id invite exist
				
				if($invites[$findId]['cnt'] < 2){ 
					
					$res = true;
					$invites[$findId]['cnt'] = 2;
					$invites[$findId]['cDate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
					
				}else{
					
					$now = $this->app['helper']('DateTimeFunc')->nowDateTime();
					$diff = strtotime($now)-strtotime($invites[$findId]['cDate']);
					
					if($diff > 1800){
						$res = true;
						unset($invites[$findId]);
					}else{
						$res = false;
					}
					
				}
				
			}
			
			
		}else{
			$res = true;
			$invites[] = ['id'=>$idInvite,
						  'cnt'=>1,
						  'cDate'=>$this->app['helper']('DateTimeFunc')->nowDateTime()];
		}
		
		$this->app['predis']['cache']->del('inviteUsers');
		$this->app['predis']['cache']->set('inviteUsers',serialize($invites));
		$this->app['predis']['cache']->expire('inviteUsers', 3600);// will be 1 hour
		
		return $res;
		
	}
	
	public function resendinvite($request){
		
		$payLoad = [];
		$idInvite = $request->get('id_invite');
		$idCompany = $request->get('id_company');
		
		if($this->app['helper']('Utility')->notEmpty($idInvite) && $this->app['helper']('Utility')->notEmpty($idCompany)){
			
			$inviteDetails = $this->InviteUser->getInvite($idInvite);

			$checkStatus = self::inviteMailQueue($idCompany.'-'.$inviteDetails['email']);
			if($checkStatus === true){

				if($inviteDetails['id_company'] == $idCompany){

					$resendInvite = self::sendInviteEmail($inviteDetails['id_maker'],
															$inviteDetails['id_company'],
															$inviteDetails['unique_code'],
															$inviteDetails['email']);

					if(isset($resendInvite['status']) && $resendInvite['status'] == 'success'){

						$this->InviteUser->updateInvite($idInvite,['tracking_id'=>$resendInvite['trackingId']]);

						$msg = $this->app['translator']->trans('add', array('%name%' => 'Invite'));
						$payLoad = ['status'=>'success','message'=>$msg];

					}else{

						$this->app['monolog.debug']->error("error occurred in send invite email",$inviteDetails);
						$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
						$payLoad = ['status'=>'error','message'=>$msg];

					}


				}else{

					$this->app['monolog.debug']->error('id company and inviter company not equal',
													   ['id invite'=>$idInvite,'id company'=>$idCompany]);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

				}
				
			}else{
				$payLoad = ['status'=>'error','message'=>'Failed to re-send invitation. Please wait 30 minutes and then try again.'];
			}
			
		}else{
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id Invite,Id Company'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}
		
		return $payLoad;
		
	}
	
	public function softwareUsers($key = '',$getParameter=array()){
		
		$payLoad = [];
		if($key === 'ed1b4747-2afb-4cf3-97b0-6280e9cbb557'){
			
			$payLoad = $this->app['component']('oAuth_Models_SoftwareUser')->allUsers($getParameter);
			
		}else{
			
			$msg = $this->app['translator']->trans('AccessDenied');
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}
		
		return $payLoad;
		
	}

	public function saveNote($postParams = array(), $id_user = 0)
    {
        $payLoad = [];

        if(!$this->app['helper']('Utility')->notEmpty($postParams['notes'])) {
            return ['status'=>'error', 'message'=>$this->app['translator']->trans('RequiredsEmpty', array('%name%' => 'notes'))];
        }

        if(!empty($id_user)) {
            $payLoad = $this->app['component']('oAuth_Models_UserNotes')->editNote($id_user, $postParams);
        }else {
            $payLoad = $this->app['component']('oAuth_Models_UserNotes')->addNote($postParams);
        }

        return $payLoad;
    }

}
