<?php
 
namespace Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
 
class LoginIp extends Command{
	
	protected $app;
	protected $ip;
	protected $idUser;
	
	public function __construct($app){
		
		$this->app = $app; 
		parent::__construct();
		
	}
	
    protected function configure()
    {
        $this
            ->setName('Ip:login')
            ->setDescription('get ip of login user.')
			->addArgument(
                'idUser',
                InputArgument::REQUIRED,
                'Which user you are?'
            )
			->addArgument(
                'ip',
                InputArgument::REQUIRED,
                'ip of user'
            );
    }
	
	protected function interact(InputInterface $input, OutputInterface $output){}
 
    protected function execute(InputInterface $input, OutputInterface $output){
		
		$this->payLoad = [];
		$this->idUser = $input->getArgument('idUser');
		$this->ip = $input->getArgument('ip');
		
		$checkDuplicate = $this->app['load']('Models_LoginIp')->checkDup($this->idUser, $this->ip);
		if(isset($checkDuplicate['data']['id'])){
			
			$cnt = $checkDuplicate['data']['cnt']+1;
			// add cnt
			$this->app['load']('Models_LoginIp')->updateIp($checkDuplicate['data']['id'], $cnt);
			
		}else{
			// add ip
			$this->app['load']('Models_LoginIp')->addIp($this->idUser, $this->ip);
		}
		
		pre('finito');
	}

}