<?php

namespace Jtl\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Jtl extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    protected $authorizationUrl = 'https://oauth.api.jtl-software.com/authorize';

    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://oauth.api.jtl-software.com/token';

    /**
     * @var string
     */
    protected $resourceOwnerDetailsUrl = 'https://oauth.api.jtl-software.com/api/v1/user';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->accessTokenUrl;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->resourceOwnerDetailsUrl;
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return string[]
     */
    protected function getDefaultScopes()
    {
        return ['profile address email phone'];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        $contentTypeRaw = $response->getHeader('Content-Type');
        $contentTypeArray = explode(';', reset($contentTypeRaw));
        $contentType = reset($contentTypeArray);

        $responseCode = $response->getStatusCode();
        $errorMessage = !empty($data['error']) ? $data['error'] : null;

        if ($responseCode >= 400) {
            throw new IdentityProviderException('Unhandled status code', $responseCode, $data);
        }

        if ($contentType !== 'application/json') {
            throw new IdentityProviderException('Unhandled content type', $contentType, $data);
        }

        if ($errorMessage) {
            throw new IdentityProviderException($errorMessage, $responseCode, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param object $response
     * @param AccessToken $token
     * @return JtlResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new JtlResourceOwner($response);
    }
}
