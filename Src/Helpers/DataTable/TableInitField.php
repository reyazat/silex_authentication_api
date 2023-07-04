<?php
namespace Helper\DataTable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Database\Capsule\Manager;

use Illuminate\Database\Query\Expression as raw;

class TableInitField{
	
	protected $app;
	public function __construct($app) {
		$this->app = $app;
	}

	public function requireTableData(){
		
		$requests = $this->app['request_content'];
		
		$data = [];
		$data['start'] = ($this->app['helper']('Utility')->notEmpty($requests->get('start')))?$requests->get('start'):0;
		$data['length'] = ($this->app['helper']('Utility')->notEmpty($requests->get('length')))?$requests->get('length'):10;
		$data['draw'] = ($this->app['helper']('Utility')->notEmpty($requests->get('draw')))?$requests->get('draw'):1;
		
		$data['search'] = (is_string($requests->get('search')))?json_decode($requests->get('search'),true):$requests->get('search');
		$data['order'] = (is_string($requests->get('order')))?json_decode($requests->get('order'),true):$requests->get('order');
		
		return $data;
		
	}

}