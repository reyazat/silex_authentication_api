<?php
namespace Component\oAuth\Models;

use Illuminate\Pagination\Paginator;

class LoginIp extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'login_ip';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	private function makeField($params = []){
		
		$fields = [];
		if(array_key_exists('user_id',$params)){
			$fields['user_id'] = $params['user_id'];
		}
		
		if(array_key_exists('ip',$params)){
			$fields['ip'] = $params['ip'];
		}
		
		if(array_key_exists('iso_code',$params)){
			$fields['iso_code'] = $params['iso_code'];
		}
		
		if(array_key_exists('device',$params)){
			$fields['device'] = $params['device'];
		}
		
		return $fields;
		
	}
	
	public function addLogin($params = []){
		
		$payLoad = [];
		$fields = $this->makeField($params);
		if(!isset($fields['user_id']) || 
		   (isset($fields['user_id']) && !$this->app['helper']('Utility')->notEmpty($fields['user_id'])) || 
		   !isset($fields['ip']) || 
		   (isset($fields['ip']) && !$this->app['helper']('Utility')->notEmpty($fields['ip']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Ip, User id'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			$fields['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
			$saveId = LoginIp::insertGetId($fields);
			
			exec('php ' . $this->app['baseDir'] . '/console ip:info '.$saveId.' "'.$fields['ip'].'" > /dev/null 2>/dev/null &');
			
			$msg = $this->app['translator']->trans('add', array('%name%' => 'Ip'));
			$payLoad = ['status'=>'success','message'=>$msg];
			
		}
		
		return $payLoad;
		
	}
	
	public function editLoginById($id = '', $params = []){
		
		$payLoad = [];
		$fields = $this->makeField($params);
		if(!$this->app['helper']('Utility')->notEmpty($id) || 
		   (isset($fields['user_id']) && !$this->app['helper']('Utility')->notEmpty($fields['user_id'])) || 
		   (isset($fields['ip']) && !$this->app['helper']('Utility')->notEmpty($fields['ip']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id, Ip, User id'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			LoginIp::where('id','=',$id)->update($fields);
			$msg = $this->app['translator']->trans('edit', array('%name%' => 'Ip'));
			$payLoad = ['status'=>'success','message'=>$msg];
			
		}
		
		return $payLoad;
		
	}
	
	public function loginHistory($params = []){
		
		$payLoad = [];
		$idCompany = $params['owner_company'];
		$idUser = $params['id_user'];
		
		if(!$this->app['helper']('Utility')->notEmpty($idCompany) || 
		   !$this->app['helper']('Utility')->notEmpty($idUser)){
	
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id Company,Id User'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
						
			// get default dataTable option
			$options = [];
			$options = $this->app['helper']('DataTable_TableInitField')->requireTableData();
		
			$currentPage = ($options['start']/$options['length'])+1;
			// Make sure that you call the static method currentPageResolver()
			// before querying users
			Paginator::currentPageResolver(function () use ($currentPage) {
				return $currentPage;
			});
			
			$LoginIp = LoginIp::select('ip','iso_code','device','created_at');
			$LoginIp = $LoginIp->where('user_id','=',$idUser);
			
			// default order by update
			$LoginIp = $LoginIp->orderby('created_at','desc');
			$LoginIp = $LoginIp->paginate($options['length'])->toArray();
	
			$result = [];
			$result['draw'] = $options['draw'];
			$result['recordsTotal'] = $LoginIp['total'];
			$result['recordsFiltered'] = $LoginIp['total'];
			$result['data'] = $LoginIp['data'];
			
			$payLoad = $result;

		}

		return $payLoad;
		
	}
	
}