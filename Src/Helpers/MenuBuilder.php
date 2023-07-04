<?php
namespace Helper;

class MenuBuilder{
	
	protected $app;
	
	public function __construct($app){
		
		$this->app = $app;
	
    }
	
	public function addToMenuSession($idUser,$IdCompany){
		
		$MenuSession = self::readUpdateMenuSession();
		$newId = $idUser.'_'.$IdCompany;
		
		if (!in_array($newId,$MenuSession)) {
			$MenuSession[] = $newId;
		}
		
		$this->app['predis']['db']->set('UpdateMenu', serialize($MenuSession));
	}
	
	private function readUpdateMenuSession(){
		
		$getSeassion = $this->app['predis']['db']->get('UpdateMenu');
		$updateUsers = unserialize($getSeassion);
		
		return $updateUsers;
		
	}
	
}