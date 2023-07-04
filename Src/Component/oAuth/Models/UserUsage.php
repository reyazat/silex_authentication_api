<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Pagination\Paginator;

class UserUsage extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'user_usage';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function tableList($filters = []){
		
		$payLoad = [];
			
		// get default dataTable option
		$options = [];
		$options = $this->app['helper']('DataTable_TableInitField')->requireTableData();

		$start = $filters[0]['value'];
		$end = $filters[1]['value'];
		
		$perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
		$currentPage = ($options['start']/$options['length'])+1;
		// Make sure that you call the static method currentPageResolver()
		// before querying users
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$selects = ['user_usage.id_user',
					//'user_usage.id_company',
					'software_user.first_name',
					'software_user.last_name',
					'software_user.email',
					'software_user.signup_source',
					'software_user.phone_code',
					'software_user.phone',
					'user_usage.updated_at'
					//'company_details.company_name',
					//'company_details.payment_status',
					//'company_details.due_date',
					//'plans.name',
					//'plans.service_name'
				   ];
		$selects[] = new raw('sum(`sm_user_usage`.`usage_sec`) as `usage`');
		$users = UserUsage::select($selects);
		$users = $users->whereRaw('`sm_user_usage`.`created_at` >= "'.$start.'%" and `sm_user_usage`.`created_at` <= "'.$end.'%"');

		// order section
		/*if(isset($options['order']) && isset($options['order'][0]) && isset($options['order'][0]['name'])){

			$users = $users->orderby($options['order'][0]['table'].'_'.$idCompany.'.'.$options['order'][0]['name'],$options['order'][0]['dir']);

		}*/

		// default order by update
		$users = $users->orderby('user_usage.updated_at','desc');
		//$users = $users->groupBy('user_usage.id_user','user_usage.id_company');
		$users = $users->groupBy('user_usage.id_user');

		$users = $users->leftJoin('software_user', 'user_usage.id_user', '=', 'software_user.identify');
		//$users = $users->leftJoin('company_details', 'user_usage.id_company', '=', 'company_details.id');
		//$users = $users->leftJoin('plans', 'company_details.id_plans', '=', 'plans.identify');

		$users = $users->paginate($options['length'])->toArray();

		$result = [];
		$result['draw'] = $options['draw'];
		$result['recordsTotal'] = $users['total'];
		$result['recordsFiltered'] = $users['total'];
		$result['data'] = $users['data'];

		$payLoad = $result;

		return $payLoad;
		
	}
	
	public function tableListByCompany($params = []){
		
		$payLoad = [];

		$start = $params['start_date'];
		$end = $params['end_date'];
		$idUser = $params['idUser'];
		
		$perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
		$selects = ['company_details.company_name',
					'company_details.payment_status',
					'company_details.due_date',
					'plans.name',
					'plans.service_name',
					'user_usage.updated_at'
				   ];
		$selects[] = new raw('sum(`sm_user_usage`.`usage_sec`) as `usage`');
		$users = UserUsage::select($selects);
		$users = $users->whereRaw('`sm_user_usage`.`created_at` >= "'.$start.'%" and `sm_user_usage`.`created_at` <= "'.$end.'%"');
		$users = $users->where('user_usage.id_user','=',$idUser);
		$users = $users->groupBy('user_usage.id_company');

		// default order by update
		$users = $users->orderby('user_usage.updated_at','desc');
		$users = $users->leftJoin('company_details', 'user_usage.id_company', '=', 'company_details.id');
		$users = $users->leftJoin('plans', 'company_details.id_plans', '=', 'plans.identify');

		$result = $users->get()->toArray();

		return $result;
		
	}
	
}