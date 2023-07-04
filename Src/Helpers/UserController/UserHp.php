<?php

namespace Helper\UserController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use \Firebase\JWT\JWT;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class UserHp
{

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
	
	public function getUserInfo($idUser = '')
    {
        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($idUser)) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
            $payLoad = $this->app['load']('Models_UserModel')->findById($idUser);
            if ($payLoad['status'] == 'Success') {
				$filename = $this->getProfilePicture($payLoad['data']['user_id']);
				$file = new File($filename);
				$payLoad['data']['last_modify'] = $file->getCtime();
                $payLoad['data']['user_settings'] =  $this->app['helper']('Utility')->decodeJson($payLoad['data']['user_settings']);
				$payLoad['pagination']['usertype_list'] = $this->app['config']['parameters']['usertype_list'];
            }
        }

        return $payLoad;
    }
	
    public function getAllUser($params = [])
    {
        $payLoad = $this->app['load']('Models_UserModel')->getAllUser($params);
		foreach($payLoad['data'] as $ky=>$row){
			$filename = $this->getProfilePicture($row['user_id']);
			$file = new File($filename);
			$payLoad['data'][$ky]['last_modify'] = $file->getCtime();
		}
		$payLoad['pagination']['usertype_list'] = $this->app['config']['parameters']['usertype_list'];
        return $payLoad;
    }
	
	public function getUsersByIDs($usersids)
    {
		$payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($usersids)){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Users IDs'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        }else{
			$payLoad = $this->app['load']('Models_UserModel')->findByIds($usersids);
			foreach($payLoad['data'] as $ky=>$row){
				$filename = $this->getProfilePicture($row['user_id']);
				$file = new File($filename);
				$payLoad['data'][$ky]['last_modify'] = $file->getCtime();
			}
		}
        return $payLoad;
    }
	
	public function addUserInfo($params = [])
    {
        $payLoad = [];
        if (
            !isset($params['username']) ||
            (isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
            !isset($params['password']) ||
            (isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password']))
        ) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Username, Password'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else if (isset($params['username']) && !$this->app['helper']('Utility')->isEmail($params['username'])) {
            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Email'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
					
			$payLoad = $this->app['load']('Models_UserModel')->signUp($params);
			if ($payLoad['status'] == 'Success') {
				$params['user_id'] = $payLoad['data']['user_id'];
				$this->app['helper']('PublicController_LoginHp')->verificationEmail($params);
			}
			
        }

        return $payLoad;
    }
	public function updateUserInfo($idUser = '', $params = [])
    {

        $payLoad = [];
        if ((isset($params['username']) && !$this->app['helper']('Utility')->notEmpty($params['username'])) ||
            (isset($params['password']) && !$this->app['helper']('Utility')->notEmpty($params['password'])) ||
            !$this->app['helper']('Utility')->notEmpty($idUser)
        ) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User, Username, Password'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
            $payLoad = $this->app['load']('Models_UserModel')->updateUserInfo($idUser, $params);
        }

        return $payLoad;
    }
	
	public function removeUser($idUser = '')
    {

        $payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($idUser)) {

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else {
			$this->profilePictureRemove($idUser);
			$credential = $this->app['request_content']->headers->get('credential');
			$cacheId = $this->app['helper']('CryptoGraphy')->urlsafe_b64encode($idUser . '-' . $credential);
            $this->app['cache']->delete($cacheId);
            $payLoad = $this->app['load']('Models_UserModel')->removeUser($idUser);
        }

        return $payLoad;
    }
	
	
	
    public function profilePictureUpload($user_id , $file_picture )
    {
		$payLoad = array();
		if (($this->app['helper']('Utility')->notEmpty($user_id))) {
			$maxFileSize = $this->app['config']['uploadrestrictions']['maxsize'];
			$allowType = $this->app['config']['uploadrestrictions']['allowedtype'];
			$totalSize = 0 ;
			$filetype = [];
			
			if ($this->app['helper']('Utility')->notEmpty($file_picture)) {
				
				$totalSize  = $file_picture->getClientSize();
				$filetype[] = $file_picture->guessExtension();
				$matches = array_intersect($filetype,$allowType);
				if($totalSize > $maxFileSize){
					$payLoad = ['status'=>'Error','message'=>$this->app['translator']->trans('413'), 'code'=>413];
				}elseif(!$this->app['helper']('Utility')->notEmpty($matches)){
					$payLoad = ['status'=>'Error','message'=>$this->app['translator']->trans('422'), 'code'=>422];
				}else{
					$this->profilePictureRemove($user_id);
					
					$this->app['helper']('Utility')->createDir($this->app['baseDir'] . '/Cache/Users/' . $user_id . '', 0777, true);
					
					$newName = 'photo'.$file_picture->getCTime().$file_picture->getClientOriginalName();
					$image = new \Gumlet\ImageResize($file_picture);
					$image->resizeToWidth(300);
					$image->save($this->app['baseDir'] . '/Cache/Users/' . $user_id .'/'. $newName);
					$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200];
				}
			} else {
				$msg = $this->app['translator']->trans('FileHasNotBeenUploaded');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
			}
		}else{
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

        return $payLoad;
    }

    public function profilePictureRemove($user_id)
    {
        $payLoad = array();
		if (($this->app['helper']('Utility')->notEmpty($user_id))) {
			$dir = $this->app['baseDir'] . '/Cache/Users/' . $user_id ;
			if ($this->app['helper']('Utility')->checkFile($dir)) {
				$finder = new \Symfony\Component\Finder\Finder();
				$finder->name('photo*')->depth('== 0');
				$finder->files()->in($dir);
				foreach ($finder as $file) {
					$this->app['helper']('Utility')->deleteFile($file->getPathName());
				}
				$this->app['helper']('Utility')->deleteFile($dir);
				$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200];
			} else {
				$msg = $this->app['translator']->trans('404');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 404];
			}
		}else{
			 $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Id User'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}
        return $payLoad;
    }

    public function getProfilePicture($user_id)
    {
		if (!$this->app['helper']('Utility')->checkFile($this->app['baseDir'] . '/Cache/Users/Avatar/profile.png')) {
			$this->app['helper']('Utility')->createDir($this->app['baseDir'] . '/Cache/Users/Avatar', 0777, true);
			$image = new \Gumlet\ImageResize($this->app['baseDir'] . '/Web/images/Avatar.png');
			$image->resizeToWidth(300);
			$image->save($this->app['baseDir'] . '/Cache/Users/Avatar/profile.png');
		}
        $resfile = '';
        if (($this->app['helper']('Utility')->notEmpty($user_id))) {
            $dir = $this->app['baseDir'] . '/Cache/Users/' . $user_id ;
			if ($this->app['helper']('Utility')->checkFile($dir)) {
				$finder = new \Symfony\Component\Finder\Finder();
				$finder->name('photo*')->depth('== 0');
				$finder->files()->in($dir);
				if ($finder->hasResults()) {
					foreach ($finder as $file) {
						
						$resfile = $file->getPathName();
					}
				} else {
					$resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/profile.png';				
				}			
			} else {
                $resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/profile.png';				
            }
        } else {
           $resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/profile.png';
			
        }	
		
        return $resfile;
    }


}