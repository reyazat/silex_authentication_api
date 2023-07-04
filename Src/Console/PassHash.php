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

class PassHash extends Command
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
            ->setName('password:hash')
            ->setDescription('make change on passwords');
    }
	
	protected function interact(InputInterface $input, OutputInterface $output){}
 
    protected function execute(InputInterface $input, OutputInterface $output){
		
		$allUsers = Manager::table('software_user')->select('id','identify','signup_source','email','password','password_hash')->get();
		pre($allUsers);
		foreach($allUsers as $user){
			
			$updateFields = [];
			if($this->app['helper']('Utility')->notEmpty($user->signup_source)){ // with signup source
				
				if (strpos($user->signup_source,'Acc-') !== false) { // accounting user
					
					if($this->app['helper']('Utility')->notEmpty($user->password_hash)){
						$updateFields['password'] = $user->password_hash;
					}else{ // error occured
						
						~d($user->id.' >>>>> '.$user->email.' >>>>> '.$user->signup_source);
					}

				}else{ // Crm user
					
					if($this->app['helper']('Utility')->notEmpty($user->password)){
						
						$decodePass = $this->app['helper']('CryptoGraphy')->md5decrypt($user->password);
						$hashPass = $this->app['helper']('CryptoGraphy')->encryptPassword($decodePass);
						
						$updateFields['password'] = $hashPass;
							
					}else{ // error occured
						~d($user->id.' >>>>> '.$user->email.' >>>>> '.$user->signup_source);
					}
					
				}
				
			}else{ // without signup source
				
				if($this->app['helper']('Utility')->notEmpty($user->password_hash)){ // accounting user
					
					$updateFields['password'] = $user->password_hash;
					$updateFields['signup_source'] = 'Acc-Software';
					
				}else if($this->app['helper']('Utility')->notEmpty($user->password)){ // Crm user
					
					$decodePass = $this->app['helper']('CryptoGraphy')->md5decrypt($user->password);
					$hashPass = $this->app['helper']('CryptoGraphy')->encryptPassword($decodePass);

					$updateFields['password'] = $hashPass;
					$updateFields['signup_source'] = 'Software';
					
				}else{
					
					// error
					~d($user->id.' >>>>> '.$user->email);
					
				}
				
			}
			
			if($this->app['helper']('Utility')->notEmpty($updateFields)){
				~d($user->identify);
				~d($updateFields);
				//$this->app['component']('oAuth_Models_SoftwareUser')->editUser($user->identify,$updateFields);
			}
			
		}
		
		pre('finito');
	}

}