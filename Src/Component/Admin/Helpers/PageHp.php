<?php
namespace Component\Admin\Helpers;

use Component\Admin\Models\Pages;
use Component\oAuth\Models\SoftwareUser;

use Helper\Utility;
use Helper\ArrayFunc;

class PageHp{
	
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function createNewPage($request){
		
		$payLoad = [];
		$pageModel = new Pages($this->app);
		$payLoad = $pageModel->insert($request);
		
		return $payLoad;
		
	}
	
	public function details($request){
		
		$payLoad = [];
		$idPage = $request->get('id');
		if(!$this->app['helper']('Utility')->notEmpty($idPage)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Page).'];
			
		}else{
			
			$pageModel = new Pages($this->app);
			$payLoad = $pageModel->details($idPage);
			
		}
		return $payLoad;
	}
	
	public function editPage($request){
		
		$idPack = $request->get('id_page');
		$payLoad = [];
		$pageModel = new Pages($this->app);
		$payLoad = $pageModel->edit($idPack,$request);
		
		return $payLoad;
		
	}
	
	public function deletePage($request){
		
		$payLoad = [];
		$idPage = $request->get('id');
		if(!$this->app['helper']('Utility')->notEmpty($idPage)){
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Page).'];
		}else{
			
			$pageModel = new Pages($this->app);
			$payLoad = $pageModel->deletePage($idPage);
			
		}
		
		return $payLoad;
		
	}
	
	public function pageList(){

		$payLoad = [];
		$pageModel = new Pages($this->app);
		$payLoad = $pageModel->pageList();
		
		return $payLoad;
		
	}
	
	public function packagePage($idPacks){
		
		$payLoad = [];
		$pageModel = new Pages($this->app);
		$payLoad = $pageModel->getPageWithIdPack($idPacks);
		
		return $payLoad;
		
	}
	
	public function validPage($idUser,$idCompany){
		
		$payLoad = [];
		
		$utility = new Utility();
		if(!$utility->notEmpty($idUser) || !$utility->notEmpty($idCompany)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id User,Id Company).'];
			
		}else{
			
			$softwareUser = new SoftwareUser($this->app);
			$userInfo = $softwareUser->getUserinfo($idUser,$idCompany);
			
			if(isset($userInfo['status']) && $userInfo['status'] == 'error'){
				
				$this->app['monolog.debug']->error('error in find user info',
												   ['id user'=>$idUser,'id company'=>$idCompany]);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}else{
				
				$packPage = self::packagePage($userInfo['id_packs']);

				if(isset($packPage['status']) && $packPage['status'] == 'success'){

					$arrayFunc = new ArrayFunc();
					$packValidPage = $arrayFunc->getSpeceficKey($packPage['pages'],'uuid','array');

					if(isset($userInfo['resources']) && $utility->notEmpty($userInfo['resources'])){

						$userResource = explode(',',$userInfo['resources']);
						$validPage = array_intersect($userResource,$packValidPage);

						$payLoad = ['status'=>'success','valid_page'=>$validPage];

					}else{
						$payLoad = ['status'=>'success','valid_page'=>$packValidPage];
					}

				}else{

					$this->app['monolog.debug']->error('error on get page for id packages',
													   ['id packs'=>$userInfo['id_packs'],
													   'res'=>$packPage]);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

				}
				
			}	
			
		}
 
		return $payLoad;

	}
	
}