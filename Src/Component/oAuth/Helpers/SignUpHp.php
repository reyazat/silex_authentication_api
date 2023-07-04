<?php

namespace Component\oAuth\Helpers;

use Component\oAuth\Models\SoftwareUser;

class SignUpHp{
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function signUpUser($request){
		$SoftwareUser = new SoftwareUser($this->app);
		$res = $SoftwareUser->insertUser($request);
		return $res;
	}
	
	public function firstLogin($token, $params = []){

		$payLoad = [];
		if(!isset($params['company_name']) || 
		   (isset($params['company_name']) && !$this->app['helper']('Utility')->notEmpty($params['company_name'])) || 
		   !isset($params['industry']) || 
		   (isset($params['industry']) && !$this->app['helper']('Utility')->notEmpty($params['industry'])) || 
		   !isset($params['payment_status']) || 
		   (isset($params['payment_status']) && !$this->app['helper']('Utility')->notEmpty($params['payment_status'])) || 
		   !isset($params['active_from']) || 
		   (isset($params['active_from']) && !$this->app['helper']('Utility')->notEmpty($params['active_from'])) || 
		   !isset($params['timezone']) || 
		   (isset($params['timezone']) && !$this->app['helper']('Utility')->notEmpty($params['timezone'])) || 
		   !isset($params['questions']) || 
		   (isset($params['questions']) && !$this->app['helper']('Utility')->notEmpty($params['questions'])) || 
		   !isset($params['locale']) || 
		   (isset($params['locale']) && !$this->app['helper']('Utility')->notEmpty($params['locale'])) || 
		   !isset($params['currency']) || 
		   (isset($params['currency']) && !$this->app['helper']('Utility')->notEmpty($params['currency']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Company Name, Industry, Active From, Timezone, Questions, Locale, Currency, Payment Status'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			$resUpdateUser = $this->app['helper']('HandlleRequest')->returnResult(
					'/user/update',
					'POST',
					['questions'=>$params['questions'],
					 'id_user'=>$params['id_user'],
					 'locale'=>$params['locale'],
					 'currency'=>$params['currency']]);
			
			$res = $this->app['helper']('Utility')->convertResponseToArray($resUpdateUser);
			
			if(isset($res['status']) && $res['status'] == 'success'){
				
				$addCompany = $this->app['helper']('HandlleRequest')->returnResult(
												'/user/company',
												'POST',
												['company_name'=>$params['company_name'],
												 'register_no'=>'',
												 'industry'=>$params['industry'],
												 'active_from'=>$params['active_from'],
												 'access_token'=>$token,
												 'id_user'=>$params['id_user'],
												 'payment_status'=>$params['payment_status'],
												  'timezone'=>$params['timezone']
												]);
				
				$payLoad = $this->app['helper']('Utility')->convertResponseToArray($addCompany);
				
			}else{
				$payLoad = $resUpdateUser;
			}
			
		}
		
		return $payLoad;
		
	}
	
}
