<?php
namespace Component\Admin\Models;

use Helper\Utility;
use Helper\DateTimeFunc;
use Helper\ArrayFunc;

use Component\Admin\Models\Pages;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Database\Capsule\Manager as Capsule;

class Menu extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'menu';
	protected $app;
	protected $utility;
	
	public function __construct($app){
		$this->app = $app;
		$this->utility = new Utility();
    }
	
	private function makeFields($request){
		
		$fields = [];
		
		if($this->utility->notEmpty($request->get('menu_name'))){
			
			$fields['menu_name'] = $request->get('menu_name');
			
		}
		
		if($this->utility->notEmpty($request->get('id_page'))){
			
			$fields['id_page'] = $request->get('id_page');
			
		}
		
		if($this->utility->notEmpty($request->get('status'))){
			
			$fields['status'] = $request->get('status');
			
		}else{
			$fields['status'] = 'Publish';
		}
		
		if($this->utility->notEmpty($request->get('id_packs'))){
			
			$idPacks = $request->get('id_packs');
			$fields['id_packs'] = implode(',',$idPacks);
			
		}else{
			
			$fields['id_packs'] = 'All';
			
		}
		
		if($this->utility->notEmpty($request->get('parent'))){
			
			$fields['parent'] = $request->get('parent');
			
		}else{
			
			$fields['parent'] = '0';
			
		}
		
		if($this->utility->notEmpty($request->get('icon'))){
			
			$fields['icon'] = $request->get('icon');
			
		}
		
		if($this->utility->notEmpty($request->get('ordering'))){
			
			$fields['ordering'] = $request->get('ordering');
			
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

		if(isset($fields['menu_name']) && $this->utility->notEmpty($fields['menu_name']) && 
		   isset($fields['id_packs']) && $this->utility->notEmpty($fields['id_packs'])){

			if(isset($fields['id_page']) && $this->utility->notEmpty($fields['id_page'])){
				
				$uuidAndUrl = self::getMenuUuidAndUrl($fields['id_page']);
				
				$fields['uuid'] = $uuidAndUrl['uuid'];
				$fields['url'] = $uuidAndUrl['url'];
				$fields['page_name'] = $uuidAndUrl['page_name'];
				
			}
			
			if(isset($fields['id_page']) && !isset($fields['uuid'])){
				
				$payLoad = ['status'=>'error','message'=>'Sorry!page with presented name not exist.'];
				
			}else{
				
				$insert = Menu::insertGetId($fields);

				if($this->utility->notEmpty($insert)){

					$payLoad = ['status'=>'success',
								'message'=>'Menu added successfully.',
								'id_menu'=>$insert];

				}else{

					$this->app['monolog.debug']->error('error in add new menu',$fields);
					$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

				}
	
			}
				
				
		}else{
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Menu Name,Id Packs).'];
		}

		
		return $payLoad;
		
	}
	
	public function edit($idMenu,$request){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idMenu)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Menu).'];
			
		}else{
			
			$fields = self::makeFields($request);
			
			if(isset($fields['id_packs']) && $this->utility->notEmpty($fields['id_packs']) && 
			  isset($fields['menu_name']) && $this->utility->notEmpty($fields['menu_name'])){
				
				if(isset($fields['id_page']) && $this->utility->notEmpty($fields['id_page'])){
				
					$uuidAndUrl = self::getMenuUuidAndUrl($fields['id_page']);

					$fields['uuid'] = $uuidAndUrl['uuid'];
					$fields['url'] = $uuidAndUrl['url'];
					$fields['page_name'] = $uuidAndUrl['page_name'];

				}else{
					
					$fields['uuid'] = NULL;
					$fields['url'] = NULL;
					$fields['page_name'] = NULL;
					$fields['id_page'] = NULL;
					
				}
				
				if(isset($fields['id_page']) && !isset($fields['uuid'])){
				
					$payLoad = ['status'=>'error','message'=>'Sorry!page with presented name not exist.'];

				}else{
					
					$update = Menu::where('id', $idMenu)
								->update($fields);

					if($this->utility->notEmpty($update)){

						$payLoad = ['status'=>'success','message'=>'Menu details updated successfully.','id_menu'=>$idMenu];

					}else{

						$this->app['monolog.debug']->error('error in edit page',['id menu'=>$idMenu,'fields'=>$fields]);
						$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];

					}
					
				}


			}else{
				
				$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Menu Name,Id Packs).'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function deleteMenu($idMenu = []){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idMenu)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Menu).'];
			
		}else{
			
			$idString = implode(',',$idMenu);
			
			$deleteMenu = Menu::whereraw('id in ('.$idString.')')->delete();
		
			if(!$this->utility->notEmpty($deleteMenu)){
				
				$payLoad = ['status'=>'error','message'=>'Sorry!an error occurred,please contact support.'];
				
			}else{
				
				$payLoad = ['status'=>'success','message'=>'Menu deleted successfully.'];
				
			}
			
		}
		
		return $payLoad;
		
	}
	
	public function getMenus(){
		
		$menuList = Menu::select('id','menu_name','id_page','page_name','id_packs','parent','ordering','icon')->get();
		
		return $menuList->toArray();
		
	}
	
	public function details($idMenu){
		
		$payLoad = [];
		if(!$this->utility->notEmpty($idMenu)){
			
			$payLoad = ['status'=>'error','message'=>'Some required fields are empty(Id Menu).'];
			
		}else{
			
			$getMenus = Menu::where('id','=',$idMenu)->get();
			if(isset($getMenus[0]) && $this->utility->notEmpty($getMenus[0])){
				$payLoad = $getMenus[0]->toArray();
			}else{
				$payLoad = [];
			}
			
			
		}
		
		return $payLoad;
	}
	
	public function menuList(){
		
		$nodeList = array();
		$tree     = array();
		
		$parents = Menu::select('id','menu_name as title','url','uuid','icon','parent','id_packs','status')
						->orderBy('parent', 'asc')
						->orderBy('ordering', 'asc')
						->get();
		
		$res = $parents->toArray();

		foreach($res as $row){
			
			$nodeList[$row['id']] = array_merge($row, array('children' => array()));
			
		}
		

		foreach ($nodeList as $nodeId => &$node) {
			if (!$node['parent'] || !array_key_exists($node['parent'], $nodeList)) {
				$tree[] = &$node;
			} else {
				$nodeList[$node['parent']]['children'][] = &$node;
			}
		}
		unset($node);
		unset($nodeList);
		
		return $tree;

		
	}
	
	public function getMenuWithIdParent($ids = []){
		
		$payLoad = [];
		$idString = implode(',',$ids);
		
		$findMenu = Menu::whereraw('parent in ('.$idString.')')->get();
		if(isset($findMenu[0]) && $this->utility->notEmpty($findMenu[0])){
			$payLoad = $findMenu->toArray();
		}else{
			$payLoad = [];
		}
		
		return $payLoad;
		
	}
	
	private function getMenuUuidAndUrl($idPage){
		
		$pages = new Pages($this->app);
		$res = $pages->getPage($idPage);
		
		return $res;
		
	}
	
}