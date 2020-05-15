<?php

namespace roydejong\SoWebApi;

use Psr\Http\Message\ResponseInterface;
use roydejong\SoWebApi\Structs\Meta\TenantStatus;
use roydejong\SoWebApi\Structs\OAuth\TokenResponse;

/**
 * Base client for the SuperOffice WebAPI.
 */
class Client
{
    const USER_AGENT = "superoffice-webapi (https://github.com/roydejong/superoffice-webapi-php-sdk)";

    protected Config $config;
    protected \GuzzleHttp\Client $httpClient;

    /**
     * Initializes the client with configuration.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->httpClient = new \GuzzleHttp\Client([
            'headers' => [
                'User-Agent' => self::USER_AGENT
            ]
        ]);
    }

    /**
     * Gets the configuration for this client.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Internal wrapper function for performing requests.
     *
     * @param string $method Request method (e.g. "GET" or "POST").
     * @param string $url Full request URL.
     * @param string|null $body
     * @return ResponseInterface
     *
     * @throws WebApiException
     */
    protected function __request(string $method, string $url, string $body = null): ResponseInterface
    {
        try {
            return $this->httpClient->request($method, $url, [
                'body' => $body
            ]);
        } catch (\Exception $ex) {
            throw new WebApiException("Error in HTTP request: {$ex->getMessage()}", $ex->getCode(), $ex);
        }
    }

    /**
     * Retrieves the Tenant Status for a SuperOffice environment.
     *
     * @see https://community.superoffice.com/en/developer/create-apps/reference/tenant-status/
     * @return TenantStatus
     */
    public function getTenantStatus(): TenantStatus
    {
        $url = "https://{$this->config->environment}.superoffice.com/api/state/{$this->config->tenantId}";
        $response = $this->__request("GET", $url);
        return new TenantStatus((string)$response->getBody());
    }

    /**
     * Determines the OAuth authorization URL.
     *
     * @param string|null $state Optional state for the URL.
     * @return string
     */
    public function getOAuthAuthorizationUrl(?string $state = null): string
    {
        $params = http_build_query([
            'client_id' => $this->config->clientId,
            'scope' => 'openid',
            'redirect_uri' => $this->config->redirectUri,
            'response_type' => 'code',
            'state' => $state
        ]);

        return "https://{$this->config->environment}.superoffice.com/login/common/oauth/authorize?{$params}";
    }

    /**
     * Determines the OAuth token request URL.
     *
     * @param string $code The authorization code received from the user callback.
     * @return string
     */
    public function getOAuthTokensUrl(string $code): string
    {
        $params = http_build_query([
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->config->redirectUri,
            'grant_type' => 'authorization_code'
        ]);

        return "https://{$this->config->environment}.superoffice.com/login/common/oauth/tokens?{$params}";
    }

    /**
     * Requests an OAuth access token for a given authorization code.
     *
     * @param string $code The authorization code received from the user callback.
     * @return TokenResponse
     *
     * @throws WebApiException
     */
    public function requestOAuthAccessToken(string $code): TokenResponse
    {
        $url = $this->getOAuthTokensUrl($code);
        $response = $this->__request("POST", $url);
        return new TokenResponse((string)$response->getBody());
    }
}