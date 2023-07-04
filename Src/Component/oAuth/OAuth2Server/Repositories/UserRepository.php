<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace OAuth2ServerExamples\Repositories;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use OAuth2ServerExamples\Entities\UserEntity;

use Component\oAuth\Models\SoftwareUser;

class UserRepository implements UserRepositoryInterface
{
    
	protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	/**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
		$SoftwareUser = new SoftwareUser($this->app);
		$findUser = $SoftwareUser->findUser($username,$password);
		
		if(isset($findUser[0])  && $this->app['helper']('Utility')->notEmpty($findUser[0])){
			
			// update last login
			$nowDateTime = $this->app['helper']('DateTimeFunc')->nowDateTime();
			$this->app['helper']('HandlleRequest')->returnResult('/user/update','POST',['id_user'=>$findUser[0]->identify,'last_login'=>$nowDateTime]);
			
			$userEntity = new UserEntity();
			$userEntity->setIdentifier($findUser[0]->identify);
			return $userEntity;
			
		}

        return;
    }
}