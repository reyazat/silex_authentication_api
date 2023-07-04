<?php
namespace Controllers;

use \Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Silex\Api\ControllerProviderInterface;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\Finder\Finder;

class PublicController implements ControllerProviderInterface 
	
{	/**
     * Application
     * 
     * @var Silex\Application 
     */
    protected $app;
	
	public function connect(Application $app){
		$this->app = $app;
		
		$index = $app['controllers_factory'];
		
		$index->get("/readme",[$this,'readme'])->bind('readme');
		$index->post("/wss",[$this,'wssConnection']);
		$index->post("/live",[$this,'liveUsers']);
		
		$index->get("/{_locale}/hello",[$this,'hello'])->bind('hello');

		
		$index->match("/test",[$this,'test'])->method('GET|POST')->bind('test');
		
		$index->get("/info",[$this,'info'])->bind('info');
		
		
		$index->get("/feedback",[$this, 'feedback'])->bind('feedback');
		
		return $index;
	}
	
	public function liveUsers(){
		
		$params = $this->app['helper']('RequestParameter')->postParameter();
		if(!isset($params['id_user']) || 
		   (isset($params['id_user']) && !$this->app['helper']('Utility')->notEmpty($params['id_user'])) || 
		   !isset($params['id_company']) || 
		   (isset($params['id_company']) && !$this->app['helper']('Utility')->notEmpty($params['id_company'])) || 
		   !isset($params['page']) || 
		   (isset($params['page']) && !$this->app['helper']('Utility')->notEmpty($params['page'])) || 
		   !isset($params['from']) || 
		   (isset($params['from']) && !$this->app['helper']('Utility')->notEmpty($params['from']))){
			
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Authorization'));
			$payLoad = ['status'=>'error','message'=>$msg];
			
		}else{
			
			if($params['from'] == 'CRM'){
				
				$this->app['component']('oAuth_Models_LiveUsers')->insertUser(['id_user'=>$params['id_user'],
																	   'id_company'=>$params['id_company'],
																	   'url'=>$params['page']]);
				
			}else if($params['from'] == 'ACC'){
				
				$this->app['component']('oAuth_Models_LiveUsersAcc')->insertUser(['id_user'=>$params['id_user'],
																				  'id_company'=>$params['id_company'],
																				  'company'=>$params['company_name'],
																				  'url'=>$params['page']]);
				
			}

			$payLoad = ['status'=>'success'];
			
		}
		
		return $this->app->json($payLoad);
	}
	
	public function wssConnection(){
		
		$postBody = file_get_contents("php://input");
		$parseBody = json_decode($postBody, true);
		
		$res = $this->app['helper']('wssHp')->receiveMsg($parseBody);
		return $this->app->json(['statusCode'=>200]);
		
	}
	
	public function readme(Request $request){
		
		$finder = new Finder();
		$finder->name('README.md')->depth('== 0');
		$finder->files()->in($this->app['baseDir']);
		foreach ($finder as $file) {
			$contents = $file->getContents();
		}
		$Parsedown = new \Parsedown();
		$contents = $Parsedown->setBreaksEnabled(true)->setMarkupEscaped(true)->setUrlsLinked(true)->text($contents , true); 					
		return $this->app['twig']->render('PublicController/Readme.twig', array(
			'content' => $contents,
		));

    }
	
	
	public function hello(Request $request){
		
		//return $this->app['translator']->trans('hello', array('%name%' => 'ali'));
		return $this->app['twig']->render('hello.twig',array('%name%' => 'ali'));

    }
	
	public function test(Application $app){
		
		
    
		$google2fa = new Google2FA();
		$secretkey = $google2fa->generateSecretKey(32);
		dump($secretkey);

		$google2fa_url = $google2fa->getQRCodeGoogleUrl(
			'tmwebseo',
			'user@yahoo.com',
			$secretkey
		);
		echo $google2fa_url;
		die;
		
		return $app->json(array('index'));
    }
	
	public function info(Request $request){
		$google2fa = new Google2FA();
		$secret = '380030';

		$valid = $google2fa->verifyKey('XSD4RVZVLU433URQW44BYXSHMJZFCKVP', $secret,0);
		
		dump($valid);
		$this->app['session']->set('foo', 'bar');
		$this->app['predis']->set('predis', 'redis');
		$this->app['predis']['db']->set('db', 'database');
		$this->app['predis']['cache']->set('cache', 'caching');

		
		
		return new Response(phpinfo(), 200, array('Cache-Control' => 's-maxage=3600, public'));

    }
	
	public function feedback(Request $request) {
		$message = $request->get('message');
		dump($message,true);
		return new Response('Thank you for your feedback!', 201);
	}
 
	
}
