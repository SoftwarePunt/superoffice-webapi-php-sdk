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

    // -----------------------------------------------------------------------------------------------------------------
    // Core & config

    protected Config $config;
    protected ?string $accessToken;
    protected \GuzzleHttp\Client $httpClient;

    /**
     * Initializes the client with configuration.
     *
     * @param Config $config
     * @param string|null $accessToken
     */
    public function __construct(Config $config, ?string $accessToken = null)
    {
        $this->config = $config;
        $this->setAccessToken($accessToken);

        $this->httpClient = new \GuzzleHttp\Client();
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
     * Sets the access token to use for future requests.
     *
     * @param string|null $accessToken
     */
    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Common request code

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
            $headers = [
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json; charset=utf-8',
                'Accept-Language' => '*'
            ];

            if ($this->accessToken) {
                $headers['Authorization'] = "Bearer {$this->accessToken}";
            }

            return $this->httpClient->request($method, $url, [
                'body' => $body,
                'headers' => $headers
            ]);
        } catch (\Exception $ex) {
            throw new WebApiException("Error in HTTP request: {$ex->getMessage()}", $ex->getCode(), $ex);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tenant status methods

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

    // -----------------------------------------------------------------------------------------------------------------
    // OAuth URLs

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
     * Determines the OAuth token request URL (authorization_code).
     *
     * @param string $code The authorization code received from the user callback.
     * @return string
     */
    public function getOAuthRequestAccessTokenUrl(string $code): string
    {
        $params = http_build_query([
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->config->redirectUri,
            'grant_type' => "authorization_code"
        ]);

        return "https://{$this->config->environment}.superoffice.com/login/common/oauth/tokens?{$params}";
    }

    /**
     * Determines the OAuth token request URL (refresh_token).
     *
     * @param string $refreshToken The refresh token acquired after initial authorization.
     * @return string
     */
    public function getOAuthRefreshAccessToken(string $refreshToken): string
    {
        $params = http_build_query([
            'grant_type' => "refresh_token",
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'refresh_token' => $refreshToken,
            'redirect_uri' => $this->config->redirectUri,
        ]);

        return "https://{$this->config->environment}.superoffice.com/login/common/oauth/tokens?{$params}";
    }

    // -----------------------------------------------------------------------------------------------------------------
    // OAuth methods

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
        $url = $this->getOAuthRequestAccessTokenUrl($code);
        $response = $this->__request("POST", $url);
        return new TokenResponse((string)$response->getBody());
    }

    /**
     * Requests a new OAuth access token for a given refresh token.
     *
     * @param string $refreshToken The refresh token acquired after initial authorization.
     * @return TokenResponse
     */
    public function refreshOAuthAccessToken(string $refreshToken): TokenResponse
    {
        $url = $this->getOAuthRefreshAccessToken($refreshToken);
        $response = $this->__request("POST", $url);
        return new TokenResponse((string)$response->getBody());
    }
}