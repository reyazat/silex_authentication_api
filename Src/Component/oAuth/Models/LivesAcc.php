<?php
namespace Component\oAuth\Models;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Pagination\Paginator;

class LivesAcc extends \Illuminate\Database\Eloquent\Model{
	
	protected $table = 'lives_acc';
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	
	public function tableList() 
    {
		$payLoad = [];
			
		// get default dataTable option
		$options = [];
		$options = $this->app['component']('Admin_Helpers_DataTable_TableInitField')->requireTableData();

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
					'lives_acc.company'];

		$users = LivesAcc::select($selects);

		// default order by update
		$users = $users->orderby('lives_acc.updated_at','desc');
		$users = $users->leftJoin('software_user', 'lives_acc.id_user', '=', 'software_user.identify');

		$users = $users->paginate($options['length'])->toArray();

        $payLoad['draw'] = $options['draw'] ? $options['draw'] : "1";
        $payLoad['recordsTotal'] = $users['total'] ? $users['total'] : 0;
        $payLoad['recordsFiltered'] = $users['total'] ? $users['total'] : 0;
        $payLoad['data'] = $users['data'] ? $users['data'] : [];

		return $payLoad;
	}
	
}