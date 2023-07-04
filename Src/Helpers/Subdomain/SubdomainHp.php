<?php
namespace Helper\Subdomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;

class SubdomainHp
{

    public function __construct($app)
    {
        $this->app = $app;
    }
	public function addsubdomain($params){
		$fields = [];
		
		$subdomain = $this->app['helper']('Utility')->filtersubdomain($params['company_name']);
		$subdomain = $this->app['load']('Models_SubdomainModel')->makeSubdomainName($subdomain,$params['company_name']);
		
		$fields['subdomain'] = $subdomain;
		
		$fields['user_id'] = $params['user_id'];
		$fields['company_name'] = $params['company_name'];
		
		$add = $this->app['load']('Models_SubdomainModel')->addSubdomain($fields);
		
		if ($add['code'] !== 200) {
			$this->app['monolog.debug']->ERROR($add['message'],['code'=>$add['code'],'status'=>$add['status']]);
		}
		return true;
	}
	
	public function getSubdomainByName($credential , $subdomain){
		
		$payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) || !$this->app['helper']('Utility')->notEmpty($subdomain)){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential , Subdomain'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else{
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($payLoad['status'] === 'Success') {			
			 	$payLoad = $this->app['load']('Models_SubdomainModel')->findByname($subdomain);
				if ($payLoad['status'] === 'Success') {
					$filename = $this->getPicture($payLoad['data']['id']);
					$file = new File($filename);
					$payLoad['data']['last_modify'] = $file->getCtime();
				}
			}
		}
	
		return $payLoad;
	}
	public function getSubdomainByUserId($user_id){
		
		$payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($user_id)){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'User Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else{
	
			$payLoad = $this->app['load']('Models_SubdomainModel')->getSubdomainByUderId($user_id);
			if ($payLoad['status'] === 'Success') {
				$filename = $this->getPicture($payLoad['data']['id']);
				$file = new File($filename);
				$payLoad['data']['last_modify'] = $file->getCtime();
			}
		}
	
		return $payLoad;
	}
	
	public function getSubdomainById($credential ,$id){
		
		$payLoad = [];
        if (!$this->app['helper']('Utility')->notEmpty($credential) || !$this->app['helper']('Utility')->notEmpty($id)){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Credential , Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else{
			$payLoad = $this->app['load']('Models_CredentialModel')->getSource($credential);
			if ($payLoad['status'] === 'Success') {	
				
				$subdomain_id = $this->app['helper']('CryptoGraphy')->urlsafe_b64decode($id);
				$subdomain_id = $this->app['helper']('CryptoGraphy')->md5decrypt($subdomain_id);
				$subdomain_id = (int) $subdomain_id;

				$payLoad = $this->app['load']('Models_SubdomainModel')->findById($subdomain_id);
				if ($payLoad['status'] === 'Success') {
					$filename = $this->getPicture($payLoad['data']['id']);
					$file = new File($filename);
					$payLoad['data']['last_modify'] = $file->getCtime();
				}
			}
		}
	
		return $payLoad;
	}
	
	public function subdomainupdate($subdomainID, $params){
		
		$payLoad = [];
        if ((isset($params['subdomain']) && !$this->app['helper']('Utility')->notEmpty($params['subdomain'])) || !$this->app['helper']('Utility')->notEmpty($subdomainID)){

            $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain Id , Subdomain Name'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
        } else{
			unset($params['id']);
			unset($params['user_id']);
			unset($params['company_name']);
			$payLoad = $this->app['load']('Models_SubdomainModel')->subdomainupdate($subdomainID, $params);
		}
	
		return $payLoad;
	}
	
	
	
	 public function pictureUpload($subdomain_id , $file_picture )
    {
		$payLoad = array();
		if (($this->app['helper']('Utility')->notEmpty($subdomain_id))) {
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
					$this->pictureRemove($subdomain_id);
					
					$this->app['helper']('Utility')->createDir($this->app['baseDir'] . '/Cache/Users/' . $subdomain_id . '', 0777, true);
					
					$newName = 'photo'.$file_picture->getCTime().$file_picture->getClientOriginalName();
					$image = new \Gumlet\ImageResize($file_picture);
					$image->resizeToWidth(200);
					$image->save($this->app['baseDir'] . '/Cache/Users/' . $subdomain_id .'/'. $newName);
					$payLoad = ['status' => 'Success', 'message' => '', 'code' => 200];
				}
			} else {
				$msg = $this->app['translator']->trans('FileHasNotBeenUploaded');
				$payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
			}
		}else{
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}

        return $payLoad;
    }
	
	
	public function pictureRemove($subdomain_id)
    {
        $payLoad = array();
		if (($this->app['helper']('Utility')->notEmpty($subdomain_id))) {
			$dir = $this->app['baseDir'] . '/Cache/Users/' . $subdomain_id ;
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
			 $msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subdomain Id'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}
        return $payLoad;
    }

	
	public function getPicture($subdomain_id)
    {
		if (!$this->app['helper']('Utility')->checkFile($this->app['baseDir'] . '/Cache/Users/Avatar/logo.png')) {
			$this->app['helper']('Utility')->createDir($this->app['baseDir'] . '/Cache/Users/Avatar', 0777, true);
			$image = new \Gumlet\ImageResize($this->app['baseDir'] . '/Web/images/logo.png');
			$image->resizeToWidth(300);
			$image->save($this->app['baseDir'] . '/Cache/Users/Avatar/logo.png');
		}
        $resfile = '';
        if (($this->app['helper']('Utility')->notEmpty($subdomain_id))) {
            $dir = $this->app['baseDir'] . '/Cache/Users/' . $subdomain_id ;
			if ($this->app['helper']('Utility')->checkFile($dir)) {
				$finder = new \Symfony\Component\Finder\Finder();
				$finder->name('photo*')->depth('== 0');
				$finder->files()->in($dir);
				if ($finder->hasResults()) {
					foreach ($finder as $file) {
						
						$resfile = $file->getPathName();
					}
				} else {
					$resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/logo.png';				
				}			
			} else {
                $resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/logo.png';				
            }
        } else {
           $resfile = $this->app['baseDir'] . '/Cache/Users/Avatar/logo.png';
			
        }	
		
        return $resfile;
    }
	
	
	
}
