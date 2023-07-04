<?php

namespace Component\oAuth\Models;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;
use Illuminate\Pagination\Paginator;

class UserUsageAcc extends \Illuminate\Database\Eloquent\Model
{

    protected $table = 'user_usage_acc';
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function tableList($filters = [])
    {
        $payLoad = [];

        // get default dataTable option
        $options = [];
        $options = $this->app['component']('Admin_Helpers_DataTable_TableInitField')->requireTableData();

        $start = $filters[0]['value'];
        $end = $filters[1]['value'];

        $perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
        $currentPage = ($options['start'] / $options['length']) + 1;
        // Make sure that you call the static method currentPageResolver()
        // before querying users
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $selects = [
            'user_usage_acc.id_user',
            'user_usage_acc.company',
            'software_user.first_name',
            'software_user.last_name',
            'software_user.email',
            'software_user.signup_source',
            'software_user.phone_code',
            'software_user.phone',
        ];

        $selects[] = new raw('sum(`sm_user_usage_acc`.`usage_sec`) as `usage`');
        $selects[] = new raw('MAX(`sm_user_usage_acc`.`live_time`) as `live_time`');
        $selects[] = new raw('MAX(`sm_user_usage_acc`.`updated_at`) as `updated_at`');

        $users = UserUsageAcc::select($selects);

        if (!empty($start) && !empty($end)) {
            if($start == $end) {
                $users = $users->whereRaw('`sm_user_usage_acc`.`created_at` like "' . $start . '%"');
            }else {
                $users = $users->whereRaw('`sm_user_usage_acc`.`created_at` >= "' . $start . '%" and `sm_user_usage_acc`.`created_at` <= "' . $end . '%"');
            }
        }

        $users = $users->leftJoin('software_user', 'user_usage_acc.id_user', '=', 'software_user.identify');
        $users = $users->orderby('user_usage_acc.updated_at', 'desc');
        $users = $users->groupBy('user_usage_acc.id_user');

        $users = $users->paginate($options['length'])->toArray();

        $payLoad['draw'] = $options['draw'] ? $options['draw'] : "1";
        $payLoad['recordsTotal'] = $users['total'] ? $users['total'] : 0;
        $payLoad['recordsFiltered'] = $users['total'] ? $users['total'] : 0;
        $payLoad['data'] = $users['data'] ? $users['data'] : [];

        return $payLoad;
    }

    public function tableListByCompany($params = [])
    {
        $payLoad = [];

        $start = $params['start_date'];
        $end = $params['end_date'];
        $idUser = $params['idUser'];

        $perfix = $this->app['config']['parameters']['mysql_params']['prefix'];
        $selects = [
            'user_usage_acc.company',
        ];

        $selects[] = new raw('sum(`sm_user_usage_acc`.`usage_sec`) as `usage`');
        $selects[] = new raw('MAX(`sm_user_usage_acc`.`live_time`) as `live_time`');
        $selects[] = new raw('MAX(`sm_user_usage_acc`.`updated_at`) as `updated_at`');

        $users = UserUsageAcc::select($selects);

        if (!empty($start) && !empty($end)) {
            if($start == $end) {
                $users = $users->whereRaw('`sm_user_usage_acc`.`created_at` like "' . $start . '%"');
            }else {
                $users = $users->whereRaw('`sm_user_usage_acc`.`created_at` >= "' . $start . '%" and `sm_user_usage_acc`.`created_at` <= "' . $end . '%"');
            }
        }

        $users = $users->where('user_usage_acc.id_user', '=', $idUser);
        $users = $users->orderby('user_usage_acc.updated_at', 'desc');
        $users = $users->groupBy('user_usage_acc.id_company');

        $payLoad = $users->get()->toArray();

        return $payLoad;
    }

}