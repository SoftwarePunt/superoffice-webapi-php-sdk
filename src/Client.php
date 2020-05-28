<?php

namespace roydejong\SoWebApi;

use Psr\Http\Message\ResponseInterface;
use roydejong\SoWebApi\Collections\Appointments\AppointmentCollection;
use roydejong\SoWebApi\Collections\Projects\ProjectCollection;
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
    protected ?string $baseUrl;
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
        $this->baseUrl = null;
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
    // Base URL

    /**
     * Gets the global base URL for the environment, ignoring the load-balanced/user-defined URL.
     * Used as the base URL for Tenant Status and OAuth processes.
     *
     * @example https://env.superoffice.com
     * @return string
     */
    public function getEnvironmentBaseUrl(): string
    {
        return "https://{$this->config->environment}.superoffice.com";
    }

    /**
     * Gets the user base URL for WebAPI requests, which is the environment URL plus Tenant ID.
     * This matches the "Endpoint" value of a TenantStatus.
     *
     * @example https://env123.superoffice.com/Cust1234
     * @return string
     */
    public function getBaseUrl(): string
    {
        if ($this->baseUrl) {
            return $this->baseUrl;
        }

        return $this->getEnvironmentBaseUrl() . "/{$this->config->tenantId}";
    }

    /**
     * Sets the user base URL for WebAPI requests.
     *
     * @param string|null $baseUrl The base URL including Tenant ID, usually from TenantStatus::$Endpoint.
     */
    public function setBaseUrl(?string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
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

            $response = $this->httpClient->request($method, $url, [
                'body' => $body,
                'headers' => $headers
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new WebApiException("Bad response: Expected 200 OK, got {$response->getStatusCode()}");
            }

            return $response;
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
        $url = "{$this->getEnvironmentBaseUrl()}/api/state/{$this->config->tenantId}";
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

        return "{$this->getEnvironmentBaseUrl()}/login/common/oauth/authorize?{$params}";
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

        return "{$this->getEnvironmentBaseUrl()}/login/common/oauth/tokens?{$params}";
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

        return "{$this->getEnvironmentBaseUrl()}/login/common/oauth/tokens?{$params}";
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

    // -----------------------------------------------------------------------------------------------------------------
    // Main utility methods

    /**
     * Performs a HTTP GET request on the WebAPI.
     *
     * @param string $path The relative path to query, e.g. "/api/v1/Project/default".
     * @return ResponseInterface
     */
    public function get(string $path): ResponseInterface
    {
        $url = $this->getBaseUrl() . $path;
        return $this->__request("GET", $url);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Collections

    public function projects(): ProjectCollection { return new ProjectCollection($this); }
    public function appointments(): AppointmentCollection { return new AppointmentCollection($this); }
}