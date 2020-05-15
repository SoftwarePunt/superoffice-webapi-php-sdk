<?php

namespace roydejong\SoWebApi;

use GuzzleHttp\Psr7\Response;
use roydejong\SoWebApi\Structs\Meta\TenantStatus;

/**
 * Base client for the SuperOffice WebAPI.
 */
class Client
{
    const USER_AGENT = "superoffice-webapi-php-sdk";

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

    protected function __request(string $method, string $url, string $body = null): Response
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
        // https://<ENV>.superoffice.com/api/state/<TENANT>

        $url = "https://{$this->config->environment}.superoffice.com/api/state/{$this->config->tenantId}";
        $response = $this->__request("GET", $url);

        if ($response->getStatusCode() !== 200) {
            throw new WebApiException("Unable to get tenant status, expected 200 OK");
        }

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
}