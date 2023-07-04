<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace OAuth2ServerExamples\Repositories;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OAuth2ServerExamples\Entities\ClientEntity;

use Component\oAuth\Models\OauthClient;

class ClientRepository implements ClientRepositoryInterface
{
    protected $app;
	
	public function __construct($app){
		$this->app = $app;
    }
	/**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
		
		//dump($clientIdentifier, $grantType, $clientSecret , $mustValidateSecret);exit;
		$checkClient = array();
		$OauthClient = new OauthClient($this->app);
		$checkClient = $OauthClient->findClient($clientIdentifier, $clientSecret);
		if (empty($checkClient)) {
            throw new \Exception('Incorrect client_id Or client_secret');
        }
		//dump($clientIdentifier, $clientSecret,$checkClient);exit;
        $clients = [
            $checkClient['client_id'] => [
                'secret'          => password_hash($checkClient['client_secret'], PASSWORD_BCRYPT),
                'client_id'       => $checkClient['client_id'],
				'name'		  => $checkClient['app_name'],
                'redirect_url'    => $checkClient['redirect_url'],
                'is_confidential' => true,
            ]
        ];
        // Check if client is registered
        if (array_key_exists($clientIdentifier, $clients) === false) {
            return;
        }
        if (
            $mustValidateSecret === true
            && $clients[$clientIdentifier]['is_confidential'] === true
            && password_verify($clientSecret, $clients[$clientIdentifier]['secret']) === false
        ) {
            return;
        }
        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($clients[$clientIdentifier]['name']);
		//$client->setSecretId($checkClient['client_secret']);
        $client->setRedirectUri($clients[$clientIdentifier]['redirect_url']);
        return $client;
    }
}