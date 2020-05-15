<?php

namespace roydejong\SoWebApiTests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Config;
use roydejong\SoWebApiTests\Mock\MockClient;

class ClientTest extends TestCase
{
    public function testSetAndGetConfig()
    {
        $configObj = new Config([
            'tenantId' => 'Cust00000'
        ]);
        $client = new Client($configObj);
        $this->assertSame($configObj, $client->getConfig());
    }

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

    public function testGetOAuthTokensUrl()
    {
        $client = new Client(new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]));

        // ...

        $actualUrl = $client->getOAuthTokensUrl('code123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/tokens?client_id=abcdef&client_secret=xyz&code=code123&redirect_uri=http%3A%2F%2Ftest-callback.com&grant_type=authorization_code";

        $this->assertSame($expectedUrl, $actualUrl);
    }

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
}