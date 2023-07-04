<?php
namespace Component\oAuth\Models;



class ForgetPass extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'forget_pass';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	public function addForgetPass($details = []){
		$payLoad = [];
		$this->app['helper']('ModelLog')->Log();
		if($this->app['helper']('Utility')->notEmpty($details)){
			
			$saveId = ForgetPass::insertGetId($details);
			if($this->app['helper']('Utility')->notEmpty($saveId)){

				$payLoad = ['status'=>'success','message'=>'Forget pass added successfully.','id'=>$saveId];

			}else{

				$this->app['monolog.debug']->error('error in add forget pass.',$details);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

			}
			
		}else{
			
			$this->app['monolog.debug']->debug('details is empty.');
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
			
		}
		
		
		return $payLoad;
		
	}
	
	public function codeInfo($code){
		$this->app['helper']('ModelLog')->Log();
		$fetchWithCode = ForgetPass::select('email')->where('code','=',$code)->get();
		if(isset($fetchWithCode[0]) && $this->app['helper']('Utility')->notEmpty($fetchWithCode[0])){
			
			return $fetchWithCode[0]->toArray();
			
		}else{
			
			return [];
			
		}
		
	}
	
}