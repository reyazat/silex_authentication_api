<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Pagination\Paginator;

class Lives extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'lives';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function tableList(){
		
		$payLoad = [];
			
		// get default dataTable option
		$options = [];
		$options = $this->app['helper']('DataTable_TableInitField')->requireTableData();

		$perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
		$currentPage = ($options['start']/$options['length'])+1;
		// Make sure that you call the static method currentPageResolver()
		// before querying users
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$selects = ['software_user.first_name',
					'software_user.last_name',
					'software_user.email',
					'software_user.signup_source',
					'software_user.phone_code',
					'software_user.phone',
					'company_details.company_name',
					'company_details.payment_status',
					'company_details.due_date',
					'plans.name',
					'plans.service_name'];
		//$selects[] = new raw('select name from sm_plans where ');
		$users = Lives::select($selects);

		// order section
		/*if(isset($options['order']) && isset($options['order'][0]) && isset($options['order'][0]['name'])){

			$users = $users->orderby($options['order'][0]['table'].'_'.$idCompany.'.'.$options['order'][0]['name'],$options['order'][0]['dir']);

		}*/

		// default order by update
		$users = $users->orderby('lives.updated_at','desc');

		$users = $users->leftJoin('software_user', 'lives.id_user', '=', 'software_user.identify');
		$users = $users->leftJoin('company_details', 'lives.id_company', '=', 'company_details.id');
		$users = $users->leftJoin('plans', 'company_details.id_plans', '=', 'plans.identify');

		$users = $users->paginate($options['length'])->toArray();

		$result = [];
		$result['draw'] = $options['draw'];
		$result['recordsTotal'] = $users['total'];
		$result['recordsFiltered'] = $users['total'];
		$result['data'] = $users['data'];

		$payLoad = $result;

		return $payLoad;
		
	}
	
}