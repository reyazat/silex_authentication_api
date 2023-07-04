<?php
 
namespace Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Expression as raw;
 
class IpInfo extends Command
{
	
	protected $app;
	public $perfix;
	
	public function __construct($app){
		
		$this->app = $app; 
		parent::__construct();
		
	}
	
    protected function configure()
    {
        $this
            ->setName('ip:info')
			->addArgument('id',InputArgument::REQUIRED,'id of ip address')
			->addArgument('ip',InputArgument::REQUIRED,'ip address');
    }
	
	protected function interact(InputInterface $input, OutputInterface $output){}
 
    protected function execute(InputInterface $input, OutputInterface $output){
		
		$id = $input->getArgument('id');
		$ip = $input->getArgument('ip');
		$checkIp = $this->app['load']('Component_oAuth_Models_IpInfo')->checkIp($ip);
		
		$isoCode = '';
		if(isset($checkIp['data']) && isset($checkIp['data']['iso_code'])){
			$isoCode = $checkIp['data']['iso_code'];
		}else{
			
			$ipInfo = \Requests::get('http://ip-api.com/json/'.$ip.'?fields=countryCode');
			$getInfoBody = $ipInfo->body;
			$decodeInfo = json_decode($getInfoBody, true);
			
			$isoCode = isset($decodeInfo['countryCode'])?$decodeInfo['countryCode']:'';
			if($this->app['helper']('Utility')->notEmpty($isoCode)){
				$this->app['load']('Component_oAuth_Models_IpInfo')->addIp(['ip'=>$ip,'iso_code'=>$isoCode]);
			}
			
		}
		
		if($this->app['helper']('Utility')->notEmpty($isoCode)){
			
			$this->app['load']('Component_oAuth_Models_LoginIp')->editLoginById($id, ['iso_code'=>$isoCode]);
			
		}
		
		pre('finito');
		
	}

}