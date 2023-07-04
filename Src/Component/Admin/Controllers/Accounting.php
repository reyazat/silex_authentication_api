<?php

namespace Component\Admin\Controllers;

use \Silex\Application;
use  \Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Component\Admin\Helpers;

class Accounting implements ControllerProviderInterface{
	
	public $app;
	
	public function connect(Application $application)
    {
        $this->app   = $application;
        $controllers = $this->app['controllers_factory'];

        $controllers->get('/allusers', function(Request $request) {
                return $this->app->json($this->app['component']('Admin_Helpers_PanelHp')->allAccUsers());
        });

        $controllers->get('/liveusers', function (Request $request) {
            return $this->app->json($this->app['component']('oAuth_Models_LivesAcc')->tableList());
        });

        $controllers->get('/userusage',
            function(Request $request){

                $payLoad = [];
                $action = $request->get('action');
                if($this->app['helper']('Utility')->notEmpty($action)) {

                    switch($action) {
                        case'user' :
                            $filters = $request->get('filtering');
                            $decodeFilter = json_decode($filters,true);
                            $payLoad = $this->app['component']('oAuth_Models_UserUsageAcc')->tableList($decodeFilter);
                            break;

                        case'company' :
                            $params = $this->app['helper']('RequestParameter')->getParameter();
                            $payLoad = $this->app['component']('oAuth_Models_UserUsageAcc')->tableListByCompany($params);
                            break;

                        default:
                            $msg = $this->app['translator']->trans('AccessDenied');
                            $payLoad = ['status'=>'error','message'=>$msg];
                    }

                }else{
                    $msg = $this->app['translator']->trans('AccessDenied');
                    $payLoad = ['status'=>'error','message'=>$msg];
                }

                return $this->app->json($payLoad);
            }
        );

        return $controllers;
    }
}

