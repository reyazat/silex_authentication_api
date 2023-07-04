<?php

namespace Component\oAuth\Helpers;

use Component\oAuth\Helpers\AuthenticateHp;
class CheckAccess {
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function run($request){
		$clientIp = $request->getClientIp();
		$access = false;
		$route = $request->getPathInfo();
		
		foreach($this->app['config']['anonymousUrlContain'] as $row){
			if (!preg_match($row, $route)){
				continue;
			}else{
				$access = true;
			}
		}
		if (in_array($clientIp, $this->app['config']['access_ip']) || $access===true){// request come from smarty view
			return true;
		}else{// request come from outside of smarty (ex: developer)

			$anonymousRoute = $this->app['config']['anonymousRoute'];

			if (!in_array($route, $anonymousRoute)){

				return $this->check_access($request);

			}else{
				return true;
			}

		}	
	}
	
	public function check_access($request) {
		$payLoad = [];

		$accessToken = (null!==$request->headers->get('Authorization'))?$request->headers->get('Authorization'):$request->get('access_token');
		$accessToken = str_replace(['Bearer','bearer'],['',''],$accessToken);
		$accessToken = $this->app['helper']('Utility')->trm($accessToken);
		//$accessToken = $request->get('access_token');
		
		if($this->app['helper']('Utility')->notEmpty($accessToken)){
			$AuthenticateHp = new AuthenticateHp($this->app);
			$res = $AuthenticateHp->validateAccessToken($request);
			if(isset($res['status']) && $res['status'] == 'error'){

				$payLoad = $res;

			}else if($res['expire'] === true ){

				$payLoad = ['status'=>'expire','message'=>'Access Token expired.','code'=>406];

			}else{

				$request->request->set('id_user', $res['user_id']);
				return true;

			}

		}else{

			$payLoad = ['status'=>'error','message'=>'Some required field are empty (Access Token).'];

		}

		return $this->app->json($payLoad);
		exit;

	}
	
	
}