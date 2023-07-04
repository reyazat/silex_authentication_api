<?php
namespace Component\oAuth\Helpers;
use PragmaRX\Google2FA\Google2FA;
use Component\oAuth\Models\SoftwareUser;


class TwoFactorHp{
	
	protected $app;
	protected $SoftwareUser;
	
	public function __construct($app){
		$this->app = $app;
		$this->SoftwareUser = new SoftwareUser($this->app);
    }
	
	public function setUpApp($request){
		$payLoad = $res = [];
		$code = 200;
		$Softwarename = $request->get('software_name');
		
		$idUser = $request->get('id_user');

		if(!$this->app['helper']('Utility')->notEmpty($idUser) && $code ===200){
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
		}else{
			$res = $this->SoftwareUser->getUserByIdentify($idUser);	
			if(isset($res['status']) && $res['status']==='error'){
				$payLoad = $res;
				$code = 404;
			}
		}
		
		if(!$this->app['helper']('Utility')->notEmpty($Softwarename) && $code ===200){
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `software_name` parameter'];
			$code = 400;
		}else if($code ===200){
			$google2fa = new Google2FA();
			$secretkey = $google2fa->generateSecretKey(32);
		
			$this->app['predis']['cache']->set('authentic'.$idUser , $secretkey);
			$this->app['predis']['cache']->expire('authentic'.$idUser , 1200);
			$this->app['predis']['cache']->ttl('authentic'.$idUser); 
		
			$google2fa_url = $google2fa->getQRCodeGoogleUrl(
				$Softwarename,
				$res['email'],
				$secretkey
			);
			$payLoad = ['status'=>'success','message'=>urlencode($google2fa_url)];
		}
		return $this->app->json($payLoad, $code);
    }
	
	public function verifyApp($request){
		$payLoad = [];
		$code = 200;
		$googleCode = $request->get('2faCode');
		$idUser = $request->get('id_user');
		
		if(!$this->app['helper']('Utility')->notEmpty($googleCode) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `2faCode` parameter'];
			$code = 400;
			
		}else if(!$this->app['helper']('Utility')->notEmpty($idUser) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
			
		}else if($this->app['predis']['cache']->exists('authentic'.$idUser) && $code ===200){
			
			$secretkey = $this->app['predis']['cache']->get('authentic'.$idUser);
			$res = self::verifycode($secretkey,$googleCode);
			if($res === true){
				
				self::savesecretkey($idUser,$secretkey,$request);
				
			}
			$payLoad = ['status'=>'success','message'=>$res];
			
		}else{
			$payLoad = ['status'=>'error','message'=>'Your QR Code has expired Please resubmit.'];
			$code = 401;
		}
		
		return $this->app->json($payLoad, $code);

	}
	
	public function verifyRequest($request){
		
		$payLoad = [];
		$code = 200;
		$googleCode = $request->get('2faCode');
		$idUser = $request->get('id_user');
		
		if(!$this->app['helper']('Utility')->notEmpty($googleCode) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `2faCode` parameter'];
			$code = 400;
			
		}else if(!$this->app['helper']('Utility')->notEmpty($idUser) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
			
		}else{
			
			$secretkey = $this->app['predis']['cache']->get('authentic'.$idUser);
			
			if(!$this->app['helper']('Utility')->notEmpty($secretkey)){
				
				$secretkey = $this->SoftwareUser->getUserField($idUser,'2factorsecret');
				
				if($this->app['helper']('Utility')->notEmpty($secretkey)){
					$this->app['predis']['cache']->set('authentic'.$idUser , $secretkey);
					$this->app['predis']['cache']->expire('authentic'.$idUser , 86400);
					$this->app['predis']['cache']->ttl('authentic'.$idUser); 
				}else{

					$this->app['monolog.debug']->warning("Some required field are empty (Google secretkey).");
					
					$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
					
					$payLoad = ['status'=>'error','message'=>$msg];
					$code = 400;	
				}
			}
			
			if($this->app['helper']('Utility')->notEmpty($secretkey) && $this->app['helper']('Utility')->notEmpty($googleCode)){
			$res = self::verifycode($secretkey,$googleCode);
			
			$payLoad = ['status'=>'success','message'=>$res];
			}else{
				$this->app['monolog.debug']->warning("Some required field are empty (Google secretkey Or googleCode).");
				
				$msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
					
					$payLoad = ['status'=>'error','message'=>$msg];
				$code = 400;	
			}
			
		}
		
		return $this->app->json($payLoad, $code);

	
	}
	
	
	public function verifyByQuestion($request){
		$payLoad = [];
		$code = 200;
		$answer = $request->get('question_answer');
		$id_user = $request->get('id_user');
		$slectedQuestion = $request->get('question');

		if($this->app['helper']('Utility')->notEmpty($id_user)){
			
		if($this->app['helper']('Utility')->notEmpty($answer) && $this->app['helper']('Utility')->notEmpty($slectedQuestion)){

			$userInfo = $this->SoftwareUser->getUserByIdentify($id_user);

			$payLoad = $this->checkCorrectAnswer($userInfo,$slectedQuestion,$answer);
			
		}else{
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameters, includes an invalid parameters value.Please check the `question` and `question_answer` parameters'];
			$code = 400;
		}
		}else{
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
		}
		return $this->app->json($payLoad, $code);
	}
	
	
	public function disableApp($request){
		$payLoad = [];
		$code = 200;
		$googleCode = $request->get('2faCode');
		$idUser = $request->get('id_user');
		
		if(!$this->app['helper']('Utility')->notEmpty($googleCode) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `2faCode` parameter'];
			$code = 400;
			
		}else if(!$this->app['helper']('Utility')->notEmpty($idUser) && $code ===200){
			
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
			
		}else{
			$secretkey = $this->app['predis']['cache']->get('authentic'.$idUser);
			if(!$this->app['helper']('Utility')->notEmpty($secretkey)){
				
				$secretkey = $this->SoftwareUser->getUserField($idUser,'2factorsecret');
			
			}
			if(!$this->app['helper']('Utility')->notEmpty($secretkey)){
			
				$payLoad = ['status'=>'error','message'=>'The `secretkey` parameter is empty.'];
				$code = 400;
			
			}else{
				$res = self::verifycode($secretkey,$googleCode);
			
				if($res === true){

					self::savesecretkey($idUser,'null',$request);
					$this->app['predis']['cache']->del('authentic'.$idUser);
				}
				$payLoad = ['status'=>'success','message'=>$res];
			}
			
		}
			
		return $this->app->json($payLoad, $code);

	}
	
	public function disableByQuestion($request){
		$payLoad = [];
		$code = 200;
		$answer = $request->get('question_answer');
		$id_user = $request->get('id_user');
		$slectedQuestion = $request->get('question');

		if($this->app['helper']('Utility')->notEmpty($id_user)){
			
		if($this->app['helper']('Utility')->notEmpty($answer) && $this->app['helper']('Utility')->notEmpty($slectedQuestion)){

			$userInfo = $this->SoftwareUser->getUserByIdentify($id_user);

			$payLoad = $this->checkCorrectAnswer($userInfo,$slectedQuestion,$answer);
			
			if($payLoad['status'] === 'success'){
					$this->savesecretkey($id_user,'null',$request);
					$this->app['predis']['cache']->del('authentic'.$id_user);
				}
		}else{
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameters, includes an invalid parameters value.Please check the `question` and `question_answer` parameters'];
			$code = 400;
		}
		}else{
			$payLoad = ['status'=>'error','message'=>'The request is missing a required parameter, includes an invalid parameter value.Please check the `id_user` parameter'];
			$code = 400;
		}
		return $this->app->json($payLoad, $code);
	}
	
	public function checkCorrectAnswer($userInfo,$slectedQuestion,$answer){
		$payLoad = [];
		$questions = unserialize($userInfo['questions']);
		$questions = array_filter($questions);

		$correctAnswer = $questions[$slectedQuestion];
		if($this->app['helper']('Utility')->trm($correctAnswer) == $this->app['helper']('Utility')->trm($answer)){
			$payLoad = ['status'=>'success','message'=>'The answer is correct.' , 'mode'=>'questions'];
		}else{
			$payLoad = ['status'=>'error','message'=>'Sorry!The answer was not correct.' , 'mode'=>'questions'];
		}
		return $payLoad;
	}
	
	public function verifycode($secretkey, $googleCode){
		$google2fa = new Google2FA();
		$valid = $google2fa->verifyKey($secretkey, $googleCode ,4);
		return $valid;
	}
	
	public function savesecretkey($idUser, $secretkey ,$request){
		
		$request->request->set('2factorsecret', $secretkey);
		
		return $this->SoftwareUser->editUser($idUser,$request);
		
	}
	
	
}