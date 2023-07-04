<?php
 
namespace Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as DB;
 
class Activity extends Command{
	
	protected $app;
	protected $idCompany;
	protected $idUser;
	
	public function __construct($app){
		
		$this->app = $app; 
		//parent::__construct();
		
	}
	
    protected function configure()
    {
        $this
            ->setName('Activity:counter')
            ->setDescription('update count of activity in menu.');
			/*->addArgument(
                'idUser',
                InputArgument::REQUIRED,
                'Which user you are?'
            )
			->addArgument(
                'idCompany',
                InputArgument::REQUIRED,
                'Which company you are?'
            );*/
    }
	
	protected function interact(InputInterface $input, OutputInterface $output){}
 
    protected function execute(InputInterface $input, OutputInterface $output){
		
		//self::setUsetID();	
		//self::changeTableName();
		//self::correctOrgTable();	
		//self::correctOrgTable2();		
		//self::correctOrgTable3();		
		//self::correctOrgTable4();		
		//self::correctOrgTable5();		
		//self::correctOrgTable6();		
		
		
		//self::addOrgTable();		
		dumper('finish');
		
		
	}
}
