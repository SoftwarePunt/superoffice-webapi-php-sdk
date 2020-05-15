<?php

namespace roydejong\SoWebApiTests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Config;
use roydejong\SoWebApiTests\Mock\MockClient;

class ClientTest extends TestCase
{
    // -----------------------------------------------------------------------------------------------------------------
    // Core & config

    public function testSetAndGetConfig()
    {
        $configObj = new Config([
            'tenantId' => 'Cust00000'
        ]);
        $client = new Client($configObj);
        $this->assertSame($configObj, $client->getConfig());
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tenant status methods

    public function testGetTenantStatus()
    {
        $testJson = '{"ContextIdentifier":"Cust12345","Endpoint":"https://sod2.superoffice.com/Cust12345","State":"Running","IsRunning":true,"ValidUntil":"2020-05-13T17:49:37.2652758Z","Api":"https://sod2.superoffice.com/Cust12345/api"}';
        $testResponse = new Response(200, ["Content-Type" => "application/json"], $testJson);

        $mockClient = new MockClient();
        $mockClient->setMockResponse($testResponse);

        // ...

        $tenantStatus = $mockClient->getTenantStatus();

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Meta\TenantStatus", $tenantStatus);

        $this->assertEquals("Cust12345", $tenantStatus->ContextIdentifier);
        $this->assertEquals("https://sod2.superoffice.com/Cust12345", $tenantStatus->Endpoint);
        $this->assertEquals("Running", $tenantStatus->State);
        $this->assertEquals(true, $tenantStatus->IsRunning);
        $this->assertEquals("2020-05-13 17:49:37", $tenantStatus->ValidUntil->format('Y-m-d H:i:s'));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // OAuth URLs

    public function testGetOAuthAuthorizationUrl()
    {
        $client = new Client(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'redirectUri' => 'http://test-callback.com',
        ]));

        // ...

        $actualUrl = $client->getOAuthAuthorizationUrl('state123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/authorize?client_id=abcdef&scope=openid&redirect_uri=http%3A%2F%2Ftest-callback.com&response_type=code&state=state123";

        $this->assertSame($expectedUrl, $actualUrl);
    }

    public function testGetOAuthRequestAccessTokenUrl()
    {
        $client = new Client(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]));

        // ...

        $actualUrl = $client->getOAuthRequestAccessTokenUrl('code123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/tokens?client_id=abcdef&client_secret=xyz&code=code123&redirect_uri=http%3A%2F%2Ftest-callback.com&grant_type=authorization_code";

        $this->assertSame($expectedUrl, $actualUrl);
    }

    public function testGetOAuthRefreshAccessToken()
    {
        $client = new Client(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]));

        // ...

        $actualUrl = $client->getOAuthRefreshAccessToken('refresh123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/tokens?grant_type=refresh_token&client_id=abcdef&client_secret=xyz&refresh_token=refresh123&redirect_uri=http%3A%2F%2Ftest-callback.com";

        $this->assertSame($expectedUrl, $actualUrl);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // OAuth methods

    public function testRequestOAuthAccessToken()
    {
        $testJson = file_get_contents(__DIR__ . '/_samples/oauth_bearer_response.json');
        $testResponse = new Response(200, ["Content-Type" => "application/json"], $testJson);

        $mockClient = new MockClient(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]));
        $mockClient->setMockResponse($testResponse);

        // ...

        $tokenResponse = $mockClient->requestOAuthAccessToken("some_code");

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\OAuth\TokenResponse", $tokenResponse);

        $this->assertEquals("Bearer", $tokenResponse->token_type);
        $this->assertEquals(3600, $tokenResponse->expires_in);

        $this->assertEquals("8A:Cust12020.AR2s3phb0gXK8DP0NfoYlsrQAQAACIJ/KQ+cbGp0l9g8PJNlBCEZxS/1hL3Cxt8ITWlQipRbdknTbIFxUuUChHj4U1qUlP6/dJA+aKE1psfT8F4XwFlYBjvw6xmM086Vckm0Mmh+fEPuoLspl+EgtQzD0F8Ka4qLFGWICvUg==",
            $tokenResponse->access_token);
        $this->assertEquals("KSamN1Tp4sd26pZJSGK6JobrWOUWorIZ2Y5XxcAqX86K9qoZRp4d3lUH32F4fiT3",
            $tokenResponse->refresh_token);
        $this->assertEquals("eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IkZyZjdqRC1hc0dpRnFBREdUbVRKZkVxMTZZdyJ9.enLw7oTQ9DkuluduWDMcdHYfmImeVNDC93txA_njdmta45ZG0VBeG9lrxInXMdxWXqb_W-ogEaHYbkfugMXwlim7V1c38Wl8QR9QVNImFACzmdma_HBILmUDK9f4XdTA93TnB-WYhesJ_tvdmzrScMIFKANvNNT3smxec6ST-j1uCUBCQrVNxILapXiUrJER4aMmAbFweWs9bbgfhR9_sQVQDmLbVw",
            $tokenResponse->id_token);
    }

    public function testRefreshOAuthAccessToken()
    {
        $testJson = file_get_contents(__DIR__ . '/_samples/oauth_refresh_response.json');
        $testResponse = new Response(200, ["Content-Type" => "application/json"], $testJson);

        $mockClient = new MockClient(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]));
        $mockClient->setMockResponse($testResponse);

        // ...

        $tokenResponse = $mockClient->refreshOAuthAccessToken("some_code");

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\OAuth\TokenResponse", $tokenResponse);

        $this->assertEquals("Bearer", $tokenResponse->token_type);
        $this->assertEquals(3600, $tokenResponse->expires_in);
        $this->assertEquals(null, $tokenResponse->refresh_token);

        $this->assertEquals("8A:Cust12345.AZuHwfgrRZPHqdjhQyay34ggAgAA8z5BnSYUSk4U4sdfr1Bjzycu0S1NC+xvghQ4VoUz9r6xpF2YAOCj0rb3LWnjLqllp3fYk8h2sxwc8d+5nb5bzGvHLHJ1UIRk38Ye4dPpmLSr4B8UaYNc9gs4Wgfgxqtii+o5fcB7lbVaVLFGmjUj1jgtIzVKiAR9eyMiWXL3dWMg+WM2Y0MOTsUrSb10kXkJ4g3M4TvH3rV4HTK3ohToxUleYvFbarx/8jeO7oLJfn3nth8NGtd1lJ",
            $tokenResponse->access_token);
        $this->assertEquals("eyJ0eFor_Demonstration_PurposeszI1NiIsIng1dCI6IkZyZjdqRC1hc0dpRnFBREdUbVRKZkVxMTZZdyJ9.For_Demonstration_PurposesbSIsImh0dHA6Ly9zY2hlbWVzLnN1cGVyb2ZmaWNlLm5ldC9pZGVudGl0eS9hGl0eS9uZXRzZXJ2ZXJfdXJsIjoiaHR0cHM6Ly9zb2Quc3VwZXJvZmZpY2UuY29tL0N1c3QyNjc1OS9SZW1vdGUvThrOFE3RG1CZ28iLCJpYXQiOiIxNTQ2NjEzMTk4IiwiaXNzIjoiaHR0cHM6Ly9zb2Quc3VwZXJvZmZpY2UuY29tqStzCXqhSjd1u7FjsJhqr1xGLDqLzkOm9_0v0nWFHESjBuPhFPIdt6lmcCuy48HGg5G0eM1_3h6SESsukXe0hNMqp3ZHjm5dCEoxE4HziLWSdRZIUa6tkP6wfHDHU_XUJu7PHo8Wx5aG9IBPZ_r1Xd8mgmt6g",
            $tokenResponse->id_token);
    }
}