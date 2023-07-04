<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace OAuth2ServerExamples\Repositories;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use OAuth2ServerExamples\Entities\RefreshTokenEntity;

use Component\oAuth\Models\RefreshToken;



class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    protected $app;
    protected $RefreshToken;
	
	public function __construct($app){
		$this->app = $app;
		$this->RefreshToken = new RefreshToken($this->app);
		
    }
	/**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface)
    {
        // Some logic to persist the refresh token in a database
		
		$accessTokenEntity = $refreshTokenEntityInterface->getAccessToken();

		$details = [];
		$details['user_identify'] = $accessTokenEntity->getUserIdentifier();
		$details['id_client'] = $accessTokenEntity->getClient()->getIdentifier();
		$details['access_token_id'] = $accessTokenEntity->getidentifier();
		$details['refresh_token_id'] = $refreshTokenEntityInterface->getidentifier();
		$details['expire_date'] = $refreshTokenEntityInterface->getExpiryDateTime()->format('Y-m-d H:i:s');
		$details['timezone'] = $refreshTokenEntityInterface->getExpiryDateTime()->getTimezone()->getName();
		$details['cdate'] = $this->app['helper']('DateTimeFunc')->nowDateTime();
		
		$removeExistRefreshToken = self::revokeRefreshTokenOptional($details['user_identify'],$details['id_client']);
		
		$saveRefreshToken = $this->RefreshToken->saveRefreshToken($details);
		
    }
    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
		return $this->RefreshToken->removeRefreshToken($tokenId);
    }
	
	public function revokeRefreshTokenOptional($userIdentify,$clientId)
    {
        // Some logic to revoke the refresh token in a database
		return $this->RefreshToken->removeRefreshTokenOptional($userIdentify,$clientId);
		
    }
    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
		
		return $this->RefreshToken->refreshTokenExist($tokenId);
		//return false;
         // The refresh token has not been revoked
    }
    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }
}