<?php
namespace Component\Admin\Helpers;

use Component\Admin\Models\Menu;

class MenuHp{
	
	protected $app;
	protected $utility;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function createNewMenu($request){
		
		$payLoad = [];
		$menuModel = new Menu($this->app);
		$payLoad = $menuModel->insert($request);
		
		return $payLoad;
		
	}
	
	public function details($request){
		
		$payLoad = [];
		$menuModel = new Menu($this->app);
		$idMenu = $request->get('id');
		$payLoad = $menuModel->details($idMenu);
		
		return $payLoad;
		
	}
	
	public function editMenu($request){
		
		$idMenu = $request->get('id_menu');
		$payLoad = [];
		$menuModel = new Menu($this->app);
		$payLoad = $menuModel->edit($idMenu,$request);
		
		return $payLoad;
		
	}
	
	public function deleteMenu($request){
		
		$payLoad = [];
		
		$id = $request->get('id');
		
		$menuModel = new Menu($this->app);
		$menuDetail = $menuModel->details($id);
		
		if(!$this->app['helper']('Utility')->notEmpty($menuDetail['parent'])){
			$idDel = $menuDetail['id'];
		}else{
			$idDel = $menuDetail['parent'];
		}
		
		$delIds = self::recursionMenuDelete([$idDel]);
		$delIds[] = $id*1;
		
		$payLoad = $menuModel->deleteMenu($delIds);
		
		return $payLoad;	
		
	}
	
	private function recursionMenuDelete($ids){
		
		$menuModel = new Menu($this->app);
		$subMenus = $menuModel->getMenuWithIdParent($ids);
		
		$res = [];
		
		if($this->app['helper']('Utility')->notEmpty($subMenus)){
			
			$idArr = [];
			foreach($subMenus as $sub){
				
				$idArr[] = $sub['id'];
				$res [] = $sub['id'];
			}
			
			$getId = self::recursionMenuDelete($idArr);
			$res = $this->app['helper']('ArrayFunc')->arrayplus($res,$getId);
		}
		
		return $res;
		
	}
	
	public function menuArray(){
		
		$payLoad = [];
		$menuModel = new Menu($this->app);
		$ress = $menuModel->menuList();
		
		return ['status'=>'success','tree'=>$ress];
		
	}
	
	public function horizontalMenu(){
		
		$payLoad = [];
		$menuArr = self::menuArray();
		$horizationMenu = self::HorizatinRecursion($menuArr['tree'],0);
		return $horizationMenu;
	
	}
	
	public function verticalMenu(){
		
		$payLoad = [];
		$menuArr = self::menuArray();
		$verticalMenu = self::VerticalRecursion($menuArr['tree'],0);
		return $verticalMenu;

	}
	
	public function menuList(){
		
		$payLoad = [];
		$menuModel = new Menu($this->app);
		$payLoad = $menuModel->getMenus();
		
		return $payLoad;
	}
	
	private function HorizatinRecursion(array $array,$n){
		
		$n++;
		
		if($n == 1){
			$ulCls = 'nav navbar-nav';
			$liCls = 'dropdown';
			$aDetails = 'class="dropdown-toggle" data-toggle="dropdown"';
			$aIcon = '<span class="caret"></span>';
		}else{
			$ulCls = 'dropdown-menu';
			$liCls = 'dropdown-submenu';
			$aDetails = '';
			$aIcon = '';
		}
		
		$menuHtml = '';
		$menuHtml = '<ul class="'.$ulCls.'">';
		foreach ($array as $key => $value){

			if (!empty($value['children']) && is_array($value['children'])){
				
					$menuHtml .= '<li class="'.$liCls.'"><a href="#" '.$aDetails.'><i class="'.$value['icon'].'"></i> '.$value['title'].' '.$aIcon.'</a>';

					$menuHtml .= self::HorizatinRecursion($value['children'],$n);

					$menuHtml .= "</li>";

			} else {

				$menuHtml .= '<li><a href="'.$value['url'].'"><i class="'.$value['icon'].'"></i> '.$value['title'].'</a></li>';

			 }
		}
		
		$menuHtml .= '</ul>';
		
		return $menuHtml;
		
	}
	
	private function VerticalRecursion(array $array,$n){
		
		$n++;
		
		if($n == 1){
			$ulCls = 'navigation navigation-main navigation-accordion';
			$aStart = '<span>';
			$aEnd = '</span>';
		}else{
			$ulCls = '';
			$aStart = '';
			$aEnd = '';
		}
		
		$menuHtml = '';
		$menuHtml = '<ul class="'.$ulCls.'">';
		foreach ($array as $key => $value){

			if (!empty($value['children']) && is_array($value['children'])){
				
					$menuHtml .= '<li><a href="#"><i class="'.$value['icon'].'"></i> '.$aStart.$value['title'].$aEnd.'</a>';

					$menuHtml .= self::VerticalRecursion($value['children'],$n);

					$menuHtml .= "</li>";

			} else {

				$menuHtml .= '<li><a href="'.$value['url'].'"><i class="'.$value['icon'].'"></i> '.$aStart.$value['title'].$aEnd.'</a></li>';

			 }
		}
		
		$menuHtml .= '</ul>';
		
		return $menuHtml;
		
	}
	
}