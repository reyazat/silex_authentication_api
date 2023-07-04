<?php
namespace Component\Admin\Models;

use Helper\Utility;
use Helper\DateTimeFunc;
use Helper\CryptoGraphy;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Database\Capsule\Manager as Capsule;

class Pages extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'pages';
	protected $app;
	protected $utility;
	
	public function __construct($app){
		$this->app = $app;
		$this->utility = new Utility();
    }
	
	private function makeFields($request){
		
		$fields = [];
		
		if($this->utility->notEmpty($request->get('page_name'))){
			
			$fields['page_name'] = $request->get('page_name');
			
		}
		
		if($this->utility->notEmpty($request->get('url'))){
			
			$fields['url'] = $request->get('url');
			
		}
		
		if($this->utility->notEmpty($request->get('id_packs'))){
			
			$idPacks = $request->get('id_packs');
			$fields['id_packs'] = implode(',',$idPacks);
			
		}else{
			
			$fields['id_packs'] = 'All';
			
		}
		
		$dateTime = new DateTimeFunc();
		$fields['cdate'] = $dateTime->nowDateTime();
		
		return $fields;

	}
	
	private function checkDuplicate($pageName){
		
		$findPage = Pages::select('id')->where('page_name','=',$pageName)->get();
		
		if(isset($findPage[0]) && $this->utility->notEmpty($findPage[0]['id'])) return $findPage[0]['id'];
		else return false;
		
	}
	
	public function insert($request){
		
		$payLoad = [];
		$this->app['helper']('ModelLog')->Log();
		
		$fields = self::makeFields($request);
		
		$crypto = new CryptoGraphy();
		$getUUID = $crypto->createUUID(1);
		if(isset($getUUID['status']) && $getUUID['status'] == 'error'){
			
			$this->app['monolog.debug']->error('error in get page uuid',['message'=>$getUUID['message']]);
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
			
		}else{
			
			$fields['uuid'] = $getUUID['uuid'];
			
			if(isset($fields['page_name']) && $this->utility->notEmpty($fields['page_name']) && 
			   isset($fields['url']) && $this->utility->notEmpty($fields['url'])){

			
				$checkDuplicate = self::checkDuplicate($fields['page_name']);
				if($this->utility->notEmpty($checkDuplicate)){

					$payLoad = ['status'=>'error','message'=>'Page with same name already exist.'];

				}else{

					$insert = Pages::insertGetId($fields);

					if($this->utility->notEmpty($insert)){

						$payLoad = ['status'=>'success',
									'message'=>'Page added successfully.',
									'id_page'=>$insert,
									'uuid'=>$fields['uuid']];

					}else{

						$this->app['monolog.debug']->error('error in add new page',$fields);
						$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

					}

				}

			}else{
				$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Page Name,Page Url).'];
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function details($idPage){
		
		$pageDetail = Pages::where('id','=',$idPage)->get();
		
		if(isset($pageDetail[0]) && $this->utility->notEmpty($pageDetail[0])){
			return $pageDetail[0]->toArray();
		}else{
			return [];
		}
		
	}
	
	public function deletePage($idPage){
		
		$payLoad = [];
		$deletePage = Pages::where('id','=',$idPage)->delete();
		if($this->utility->notEmpty($deletePage)){
			$payLoad = ['status'=>'success','message'=>'page deleted successfully'];
		}else{
			$this->app['monolog.debug']->error('error in delete page',['id page'=>$idPage]);
			$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
		}
		
		return $payLoad;
	}
	
	public function edit($idPage,$request){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idPage)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Page).'];
			
		}else{
			
			$fields = self::makeFields($request);
			if(isset($fields['page_name']) && $this->utility->notEmpty($fields['page_name']) && 
			  isset($fields['url']) && $this->utility->notEmpty($fields['url'])){
				
				$checkDuplicate = self::checkDuplicate($fields['page_name']);
				if(!$this->utility->notEmpty($checkDuplicate) || ($checkDuplicate == $idPage)){

					$update = Pages::where('id', $idPage)
									->update($fields);

					if($this->utility->notEmpty($update)){

						$payLoad = ['status'=>'success','message'=>'Page details updated successfully.','id_page'=>$idPage];

					}else{

						$this->app['monolog.debug']->error('error in edit page',['id page'=>$idPage,'fields'=>$fields]);
						$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

					}

				}else{

					$payLoad = ['status'=>'error','message'=>'Page with same details already exist.'];

				}
				
			}else{
				
				$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Page Name,Page Url).'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function pageList(){
		
		$lists = Capsule::table("pages")->select('pages.id','pages.page_name','pages.url','pages.uuid',
												new raw('group_concat(sm_packs.pack_name) as package'))
								->leftjoin("packs",Capsule::raw("FIND_IN_SET(sm_packs.id,sm_pages.id_packs)"),">",Capsule::raw("'0'"))
								->groupBy('pages.page_name')
								->get();
		
		return $lists->toArray();
		
	}
	
	public function getPage($idPage){
		
		$getPage = Pages::select('page_name','uuid','url')
						->where('id','=',$idPage)
						->get();
		if(isset($getPage[0]) and $this->utility->notEmpty($getPage[0])){
			
			return $getPage[0]->toArray();
			
		}else{
			return [];
		}
		
		
	}
	
	public function getPageWithIdPack($idPacks){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idPacks)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Packs).'];
			
		}else{
			
			$idPackArr = [];
			$idPackArr = explode(',',$idPacks);
			$idPackArr = array_filter($idPackArr);
			
			$getPages = Pages::select('page_name','uuid');
			foreach($idPackArr as $id){
				
				$getPages = $getPages->orwhereRaw("find_in_set({$id},id_packs)");
				
			}
			$getPages = $getPages->orwhereRaw('id_packs = "All"');
			$getPages = $getPages->get();
			
			$payLoad = ['status'=>'success','pages'=>$getPages->toArray()];
			
		}
		
		return $payLoad;
	} 
	
}