<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace OAuth2ServerExamples\Repositories;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use OAuth2ServerExamples\Entities\AccessTokenEntity;

use Component\oAuth\Models\AccessToken;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    
	protected $app;
	protected $AccessToken;
	
	public function __construct($app){
		$this->app = $app;
		$this->AccessToken =  new AccessToken($this->app);
		
    }
	/**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        // Some logic here to save the access token to a database

		$details = [];
		$details['user_identify'] = $accessTokenEntity->getUserIdentifier();
		$details['id_client'] = $accessTokenEntity->getClient()->getIdentifier();
		$details['access_token_id'] = $accessTokenEntity->getidentifier();
		$details['expire_date'] = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
		$details['timezone'] = $accessTokenEntity->getExpiryDateTime()->getTimezone()->getName();
		$details['cdate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
		
		$removeExistAccessToken = self::revokeAccessTokenOptional($details['user_identify'],$details['id_client']);
		
		$saveAccessToken = $this->AccessToken->saveAccessToken($details);
    }
    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        // Some logic here to revoke the access token
		return $this->AccessToken->removeAccessToken($tokenId);
    }
	
	public function revokeAccessTokenOptional($userIdentify,$ClientId)
	{
		
		return $this->AccessToken->removeAccessTokenOptional($userIdentify,$ClientId);
		
	}
    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
		
		return $this->AccessToken->accessTokenExist($tokenId);
		
        //return false; // Access token hasn't been revoked
    }
    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);
	
        return $accessToken;
    }
}