<?php
namespace Component\Admin\Models;

use Helper\Utility;
use Helper\DateTimeFunc;

class Packs extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'packs';
	protected $app;
	protected $utility;
	
	public function __construct($app){
		$this->app = $app;
		$this->utility = new Utility();
    }
	
	private function makeFields($request){
		
		$fields = [];
		
		if($this->utility->notEmpty($request->get('pack_name'))){
			
			$fields['pack_name'] = $request->get('pack_name');
			
		}
		
		if($this->utility->notEmpty($request->get('mood'))){
			
			$fields['mood'] = $request->get('mood');
			
		}else{
			
			$fields['mood'] = 'Show';
			
		}
		
		$dateTime = new DateTimeFunc();
		$fields['cdate'] = $dateTime->nowDateTime();
		
		return $fields;

	}
	
	private function checkDuplicate($packName){
		
		$findPack = Packs::select('id')->where('pack_name','=',$packName)->get();
		
		if(isset($findPack[0]) && $this->utility->notEmpty($findPack[0]['id'])) return $findPack[0]['id'];
		else return false;
		
	}
	
	public function insert($request){
		
		$this->app['helper']('ModelLog')->Log();
		
		$fields = self::makeFields($request);
		
		$payLoad = [];
		if(isset($fields['pack_name']) && $this->utility->notEmpty($fields['pack_name'])){
			
			$checkDuplicate = self::checkDuplicate($fields['pack_name']);
			if($this->utility->notEmpty($checkDuplicate)){
				
				$payLoad = ['status'=>'error','message'=>'Pack with same name already exist.'];
				
			}else{
				
				$insert = Packs::insertGetId($fields);
			
				if($this->utility->notEmpty($insert)){

					$payLoad = ['status'=>'success','message'=>'Pack added successfully.','id_pack'=>$insert];

				}else{

					$this->app['monolog.debug']->error('error in add new pack',$fields);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

				}
				
			}
			
		}else{
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Pack Name).'];
		}

		return $payLoad;
		
	}
	
	public function edit($idPack,$request){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idPack)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Pack).'];
			
		}else{
			
			$fields = self::makeFields($request);
			if(isset($fields['pack_name']) && $this->utility->notEmpty($fields['pack_name'])){
				
				$checkDuplicate = self::checkDuplicate($fields['pack_name']);
				if(!$this->utility->notEmpty($checkDuplicate) || ($checkDuplicate == $idPack)){

					$update = Packs::where('id', $idPack)
									->update($fields);

					if($this->utility->notEmpty($update)){

						$payLoad = ['status'=>'success','message'=>'Pack details updated successfully.','id_pack'=>$idPack];

					}else{

						$this->app['monolog.debug']->error('error in edit pack',['id pack'=>$idPack,'fields'=>$fields]);
						$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

					}

				}else{

					$payLoad = ['status'=>'error','message'=>'Pack with same details already exist.'];

				}
				
			}else{
				
				$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Pack Name).'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function deletePack($idPack){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idPack)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Pack).'];
			
		}else{
			
			$delete = Packs::where('id','=',$idPack)->delete();
			
			if($this->utility->notEmpty($delete)){
				
				$payLoad = ['status'=>'success','message'=>'Pack deleted successfully.'];
				
			}else{
				
				$this->app['monolog.debug']->error('error in delete pack',['id pack'=>$idPack]);
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function packList(){

		$lists = Packs::select('id','pack_name','mood')->get();
		return $lists->toArray();
		
	}
	
	public function packDetails($idPack){
		
		$findPack = Packs::where('id','=',$idPack)->get();
		if(isset($findPack[0]) && $this->utility->notEmpty($findPack[0])){
			return $findPack[0]->toArray();
		}else{
			return [];
		}
		
	}
	
	public function packDetailsByName($name){
		
		$findPack = Packs::where('pack_name','=',$name)->get();
		if(isset($findPack[0]) && $this->utility->notEmpty($findPack[0])){
			return $findPack[0]->toArray();
		}else{
			return [];
		}
		
	}
	
}