<?php
namespace Models;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Expression as raw;

class CredentialModel extends \Illuminate\Database\Eloquent\Model{

	protected $table = 'credential';
	
	protected $app;
	public function __construct($app){
		$this->app = $app;
    }
	
	public function getSource($credential = ''){
		
		$payLoad = [];
		if(!$this->app['helper']('Utility')->notEmpty($credential)){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential'));
			$payLoad = ['status'=>'Error','message'=>$msg,'code'=>400];
			
		}else{
			if(!$this->app['helper']('Utility')->notEmpty($this->app['cache']->fetch('db_'.$credential))){
				$getCredential = CredentialModel::select('source')->where('credential','=',$credential)->first();
				if($this->app['helper']('Utility')->notEmpty($getCredential)){
					$getCredential =  $getCredential->toArray();
					$this->app['cache']->store('db_'.$credential, $this->app['helper']('Utility')->encodeJson($getCredential), 86400);
					$payLoad = ['status'=>'Success','message'=>'','code'=>200,'data'=>$getCredential];
				}else{
					$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential'));
					$payLoad = ['status'=>'Error','message'=>$msg,'code'=>400];
				}
			}else{
				$getCredential = $this->app['cache']->fetch('db_'.$credential);
				$getCredential = $this->app['helper']('Utility')->decodeJson($getCredential);
				$payLoad = ['status'=>'Success','message'=>'','code'=>200, 'data'=>$getCredential];
			}
			
		}
		
		return $payLoad;
		
	}
	
}