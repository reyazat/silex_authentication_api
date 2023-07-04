<?php
namespace Component\Admin\Helpers;

use Component\Admin\Models\Packs;

class PackHp{
	
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function createNewPack($request){
		
		$payLoad = [];
		$packModel = new Packs($this->app);
		$payLoad = $packModel->insert($request);
		
		return $payLoad;
		
	}
	
	public function editPack($request){
		
		$idPack = $request->get('id_pack');
		$payLoad = [];
		$packModel = new Packs($this->app);
		$payLoad = $packModel->edit($idPack,$request);
		
		return $payLoad;
		
	}
	
	public function deletePack($request){
		
		$idPack = $request->get('id_pack');
		$payLoad = [];
		$packModel = new Packs($this->app);
		$payLoad = $packModel->deletePack($idPack);
		
		return $payLoad;
		
	}
	
	public function packDetailsByName($name){
		
		$payLoad = [];
		$packModel = new Packs($this->app);
		$payLoad = $packModel->packDetailsByName($name);
		
		return $payLoad;
		
	}
	
	public function packDetails($request){
		
		$payLoad = [];
		$idPack = $request->get('id_pack');
		if(!$this->app['helper']('Utility')->notEmpty($idPack)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Pack).'];
			
		}else{
			
			$packModel = new Packs($this->app);
			$payLoad = $packModel->packDetails($idPack);
			
		}
		
		return $payLoad;
		
	}
	
	public function packList(){
		
		$payLoad = [];
		$packModel = new Packs($this->app);
		$payLoad = $packModel->packList();
		
		return $payLoad;
		
	}
	
}