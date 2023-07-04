<?php
namespace Component\Admin\Helpers;

class PanelHp{
	
	protected $app;
	
	public function __construct($app) {
		$this->app = $app;
    }
	
	public function signupUsers() {
		return $this->app['component']('oAuth_Models_SoftwareUser')->signupUsers();
	}

    public function allAccUsers() {
        return $this->app['component']('oAuth_Models_SoftwareUser')->allAccUsers();
    }
}