<?php
namespace Helper\DeviceController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DeviceHp
{

    public function __construct($app)
    {
        $this->app = $app;
    }
	
	
    public function getDeviceToken($idUser)
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($idUser)) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
            $payLoad = $this->app['load']('Models_DeviceTokenModel')->getDeviceTokenByUserId($idUser);
        }
        return $payLoad;
    }
	
	public function getDeviceTokenByIDS($UserIDs)
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($UserIDs)) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User Ids'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
            $payLoad = $this->app['load']('Models_DeviceTokenModel')->getDeviceTokenByUserIds($UserIDs);
        }
        return $payLoad;
    }
	
	public function deviceListHp($credential = '', $token = '')
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) ||            
            !$this->app['helper']('Utility')->notEmpty($token) 
			) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Token'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {

            $checkAccess = $this->app['helper']('JWTHp')->verifyToken($token);

            if ($checkAccess['status'] === 'Success') {
				$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
				if ($getLoginSource['status'] === 'Success') {
					
					$getdata = $this->app['load']('Models_DeviceTokenModel')->returnRows([],['user_id','device_token','device_type']);
					$payLoad = (['status' => 'Success', 'message' => '', 'code'=>200, 'data' =>$getdata ]);
				} else {
					$payLoad = $getLoginSource;
				}
            } else {
                $payLoad = $checkAccess;
            }
        }
		
        return $payLoad;
    }
	public function delDeviceHp($device_token, $credential = '', $token = '')
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) ||            
            !$this->app['helper']('Utility')->notEmpty($token) || 
			!$this->app['helper']('Utility')->notEmpty($device_token)
			) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Device Token, Credential, Token'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {

            $checkAccess = $this->app['helper']('JWTHp')->verifyToken($token);

            if ($checkAccess['status'] === 'Success') {
				$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
				if ($getLoginSource['status'] === 'Success') {
					 $payLoad = $this->app['load']('Models_DeviceTokenModel')->deleteRows([['device_token','=',$device_token]]);
				} else {
					$payLoad = $getLoginSource;
				}
            } else {
                $payLoad = $checkAccess;
            }
        }
		
        return $payLoad;
    }
	
	public function addDeviceHp($credential = '', $params )
    {
		$payLoad = [];
		if (!$this->app['helper']('Utility')->notEmpty($credential) ||            
            !isset($params['device_token']) ||
			(isset($params['device_token']) &&!$this->app['helper']('Utility')->notEmpty($params['device_token'])) || 
			!isset($params['device_type']) ||
			(isset($params['device_type']) &&!$this->app['helper']('Utility')->notEmpty($params['device_type'])) 
			) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential, Device token, Device type'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			
			$getLoginSource = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($getLoginSource['status'] === 'Success') {
				$add_device = $this->app['load']('Models_DeviceTokenModel')->addDeviceToken($params);
				$payLoad = (['status' => 'Success', 'message' => '', 'code' =>200 ]);
			}else{
				$payLoad = $getLoginSource;
			}
		}
		return $payLoad;
	
	}
    

}
