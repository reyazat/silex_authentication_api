<?php 
namespace Helper;

use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Aws\Credentials\Credentials;

class wssHp{
	
	protected $app;
	public function __construct($app){
		
		$this->app = $app; 
		
	}
	
	public function sendMsg($idConnection = '', $data = []){
		
		$AccessKey = $this->app['config']['parameters']['wss']['AccessKey'];
		$SecretKey = $this->app['config']['parameters']['wss']['SecretKey'];
		$region = $this->app['config']['parameters']['wss']['region'];
		$service = $this->app['config']['parameters']['wss']['service'];
		$stage = $this->app['config']['parameters']['wss']['stage'];
		$idApi = $this->app['config']['parameters']['wss']['idApi'];
		
		$connectionUrl = 'https://'.$idApi.'.'.$service.'.'.$region.'.amazonaws.com/'.$stage.'/@connections/'.$idConnection;
		
		$credentials = new Credentials($AccessKey, $SecretKey);
		$msg = json_encode($data); 
		
		$headers = array('Content-Type => application/x-www-form-urlencoded');
		$request = new Request('POST', $connectionUrl, ['Content-Type' => 'application/json'], $msg);
		
		$signer = new SignatureV4($service, $region); 
		$request = $signer->signRequest($request, $credentials);
		
		//$headers = array('Content-Type => application/x-www-form-urlencoded');
		$client = new Client([ 'headers' => $headers]);
		$response = $client->send($request);
		$result = $response->getBody();
		
		return true;
	}
	
	public function receiveMsg($params = []){
		
		$myfile = fopen($this->app['baseDir'].'/Web/wssLog-'.date('Ymd').'.txt', "a+");
		$txt = 'Time: '.date('H:i:s');
		$txt .= "\n";
		fwrite($myfile, $txt);
		
		if(isset($params['connectionId'])){
			
			$idConnection = $params['connectionId'];
			if(isset($params['body'])){
				
				if(isset($params['body']['action']) && $params['body']['action'] == 'dispatcher'){

					$sendData = [];
					$data = isset($params['body']['data'])?$params['body']['data']:'';

					$tokenCode = isset($data['token'])?$data['token']:'';
					$from = isset($data['from'])?$data['from']:'';
					
					if($this->app['helper']('Utility')->notEmpty($tokenCode)){
						
						$decodeToken = $this->app['helper']('CryptoGraphy')->urlsafe_b64decode($tokenCode);
						$token = $this->app['helper']('CryptoGraphy')->md5decrypt($decodeToken);
						
						$expoToken = explode('-',$token);
						$idCompany = isset($expoToken[0])?$expoToken[0]:'';
						$idUser = isset($expoToken[1])?$expoToken[1]:'';
						
						if($this->app['helper']('Utility')->notEmpty($idCompany) && 
						   $this->app['helper']('Utility')->notEmpty($idUser)){
							
							$state = isset($data['state'])?$data['state']:'';
							switch($state){

								case'whoAmI' :

									$this->app['load']('Models_WssModel')->CreateOrUpdate(['id_company'=>$idCompany,'id_user'=>$idUser,'source'=>$from,'id_socket'=>$idConnection]);
									
									$sendData['status'] = 'Success';
									$sendData['code'] = '200';
									$sendData['message'] = $this->app['translator']->trans('200', array());

								break;
									
								case'live' :
									$page = isset($data['page'])?$data['page']:'';
									$sendData['status'] = 'Success';
									$sendData['code'] = '200';
									$sendData['url'] = $page;
									$sendData['message'] = $this->app['translator']->trans('200', array());
								break;
									
								case'counter' :
									
									$this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['view'].'system/counter',[],['id_user'=>$idUser,'id_company'=>$idCompany,'id_socket'=>$idConnection],false);
									
									$sendData['status'] = 'Success';
									$sendData['code'] = '200';
									$sendData['message'] = $this->app['translator']->trans('200', array());
								break;

								default:// state not defined
									$sendData['status'] = 'Error';
									$sendData['code'] = '400';
									$sendData['message'] = $this->app['translator']->trans('400', array());

							}

							$sendData['state'] = $state;
							$sendData['id_connection'] = $idConnection;	

							$txt = 'Send Data: '.json_encode($sendData);
							$txt .= "\n";
							fwrite($myfile, $txt);
							
						}else{
							$sendData['status'] = 'Error';
							$sendData['code'] = '401';
							$sendData['message'] = $this->app['translator']->trans('400', array());
						}
						
					}else{
						
						$sendData['status'] = 'Error';
						$sendData['code'] = '400';
						$sendData['message'] = $this->app['translator']->trans('400', array());
						
					}
					
				}else{ // wrong route
					
				}
				
			}else{ // wrong data format
				$sendData['status'] = 'Error';
				$sendData['code'] = '400';
				$sendData['message'] = $this->app['translator']->trans('400', array());
			}
			
			$this->sendMsg($idConnection, $sendData);
			
			if(isset($params['connectionId'])){
				$txt = 'Id connection: '.$params['connectionId'];
				$txt .= "\n";
				fwrite($myfile, $txt);
			}
			if(isset($params['body'])){
				$txt = 'Body: '.json_encode($params['body']);
				$txt .= "\n";
				fwrite($myfile, $txt);
			}
			
			$txt = 'All receive data: '.json_encode($params);
			$txt .= "\n";
			$txt .= '---------------';
			$txt .= "\n";
			
			
		}else{ // message not have connection id (wrong route)
			$txt = 'Wrong route';
			$txt .= "\n";
			$txt .= '---------------';
			$txt .= "\n";
		}
		
		fwrite($myfile, $txt);
		fclose($myfile);
		
		return true;
		
	}
	
}
