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
        $configObj = new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'redirectUri' => 'http://test-callback.com',
        ]);

        $client = new Client($configObj);

        $actualUrl = $client->getOAuthAuthorizationUrl('state123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/authorize?client_id=abcdef&scope=openid&redirect_uri=http%3A%2F%2Ftest-callback.com&response_type=code&state=state123";

        $this->assertSame($expectedUrl, $actualUrl);
    }

    public function testGetOAuthTokensUrl()
    {
        $configObj = new Config([
            'environment' => 'env-name',
            'clientId' => 'abcdef',
            'clientSecret' => 'xyz',
            'redirectUri' => 'http://test-callback.com',
        ]);

        $client = new Client($configObj);

        $actualUrl = $client->getOAuthTokensUrl('code123');
        $expectedUrl = "https://env-name.superoffice.com/login/common/oauth/tokens?client_id=abcdef&client_secret=xyz&code=code123&redirect_uri=http%3A%2F%2Ftest-callback.com&grant_type=authorization_code";

        $this->assertSame($expectedUrl, $actualUrl);
    }
}