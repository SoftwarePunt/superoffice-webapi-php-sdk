<?php

namespace SoftwarePunt\SoWebApiTests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SoftwarePunt\SoWebApi\Client;
use SoftwarePunt\SoWebApi\Config;
use SoftwarePunt\SoWebApiTests\Mock\MockClient;

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

    public function testSetAccessToken()
    {
        $testToken = "my_bearer_token";
        $request = null;

        $client = new MockClient();
        $client->setAccessToken($testToken);
        $client->setMockHandler(function (RequestInterface $_request) use ($testToken, &$request) {
            $request = $_request;
            return new Response(200);
        });
        $client->getTenantStatus();

        if ($request) {
            $this->assertSame(
                $request->getHeader("Authorization")[0],
                "Bearer {$testToken}"
            );
        } else {
            $this->markTestIncomplete('Mock request handler did not fire');
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Base URL

    public function testGetEnvironmentBaseUrl()
    {
        $client = new Client(new Config([
            'environment' => 'sod-bla-bla'
        ]));

        $this->assertSame("https://sod-bla-bla.superoffice.com", $client->getEnvironmentBaseUrl(),
            "Environment base URL should use configured environment name");
    }

    public function getSetAndGetBaseUrl()
    {
        $client = new Client(new Config([
            'environment' => 'sod-bla-bla'
        ]));
        $client->setBaseUrl("https://my.custom.url/abc123");

        $this->assertSame("https://sod-bla-bla.superoffice.com", $client->getEnvironmentBaseUrl(),
            "getEnvironmentBaseUrl() should ignore the user-provided URL");
        $this->assertSame("https://my.custom.url/abc123", $client->getBaseUrl(),
            "getBaseUrl() should return the user-provided value from setBaseUrl(), without any changes");
    }

    public function testGetBaseUrlFallback()
    {
        $client = new Client(new Config([
            'environment' => 'sod-bla-bla',
            'tenantId' => 'Tenant123'
        ]));

        $expectedBaseUrl = "https://sod-bla-bla.superoffice.com/Tenant123";

        $this->assertSame($expectedBaseUrl, $client->getBaseUrl(),
            "getBaseUrl() fallback should equal \"getEnvironmentBaseUrl() plus Tenant ID\"");
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

        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\Meta\TenantStatus", $tenantStatus);

        $this->assertEquals("Cust12345", $tenantStatus->ContextIdentifier);
        $this->assertEquals("https://sod2.superoffice.com/Cust12345", $tenantStatus->Endpoint);
        $this->assertEquals("Running", $tenantStatus->State);
        $this->assertEquals(true, $tenantStatus->IsRunning);
        $this->assertEquals("2020-05-13 17:49:37", $tenantStatus->ValidUntil->format('Y-m-d H:i:s'));

        // ...

        $mockClient->setBaseUrl($tenantStatus->Endpoint);
        $this->assertSame("https://sod2.superoffice.com/Cust12345", $mockClient->getBaseUrl());
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

        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\OAuth\TokenResponse", $tokenResponse);

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

        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\OAuth\TokenResponse", $tokenResponse);

        $this->assertEquals("Bearer", $tokenResponse->token_type);
        $this->assertEquals(3600, $tokenResponse->expires_in);
        $this->assertEquals(null, $tokenResponse->refresh_token);

        $this->assertEquals("8A:Cust12345.AZuHwfgrRZPHqdjhQyay34ggAgAA8z5BnSYUSk4U4sdfr1Bjzycu0S1NC+xvghQ4VoUz9r6xpF2YAOCj0rb3LWnjLqllp3fYk8h2sxwc8d+5nb5bzGvHLHJ1UIRk38Ye4dPpmLSr4B8UaYNc9gs4Wgfgxqtii+o5fcB7lbVaVLFGmjUj1jgtIzVKiAR9eyMiWXL3dWMg+WM2Y0MOTsUrSb10kXkJ4g3M4TvH3rV4HTK3ohToxUleYvFbarx/8jeO7oLJfn3nth8NGtd1lJ",
            $tokenResponse->access_token);
        $this->assertEquals("eyJ0eFor_Demonstration_PurposeszI1NiIsIng1dCI6IkZyZjdqRC1hc0dpRnFBREdUbVRKZkVxMTZZdyJ9.For_Demonstration_PurposesbSIsImh0dHA6Ly9zY2hlbWVzLnN1cGVyb2ZmaWNlLm5ldC9pZGVudGl0eS9hGl0eS9uZXRzZXJ2ZXJfdXJsIjoiaHR0cHM6Ly9zb2Quc3VwZXJvZmZpY2UuY29tL0N1c3QyNjc1OS9SZW1vdGUvThrOFE3RG1CZ28iLCJpYXQiOiIxNTQ2NjEzMTk4IiwiaXNzIjoiaHR0cHM6Ly9zb2Quc3VwZXJvZmZpY2UuY29tqStzCXqhSjd1u7FjsJhqr1xGLDqLzkOm9_0v0nWFHESjBuPhFPIdt6lmcCuy48HGg5G0eM1_3h6SESsukXe0hNMqp3ZHjm5dCEoxE4HziLWSdRZIUa6tkP6wfHDHU_XUJu7PHo8Wx5aG9IBPZ_r1Xd8mgmt6g",
            $tokenResponse->id_token);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Main utility methods

    public function testGet()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$mockAssertionFired, &$request) {
            $request = $_request;
            return new Response(200);
        });
        $response = $client->get('/bla');

        $this->assertInstanceOf("Psr\Http\Message\ResponseInterface", $response);

        if ($request) {
            $this->assertEmpty($request->getHeader("Authorization"),
                "Authorization header should not be set if setAccessToken() is never called");
            $this->assertSame("application/json; charset=utf-8", $request->getHeader("Accept")[0],
                "Accept header should be set to json");
            $this->assertSame("*", $request->getHeader("Accept-Language")[0],
                "Accept-Language header should be set to wildcard");
            $this->assertSame('https://mock.superoffice.com/Cust12345/bla', (string)$request->getUri(),
                "Generated request URL should automatically include base URL");
        } else {
            $this->markTestIncomplete('Mock request handler did not fire');
        }
    }
    public function testGetWithOptions()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$mockAssertionFired, &$request) {
            $request = $_request;
            return new Response(200);
        });

        $client->setAccessToken("ABC");

        $response = $client->get('/bla', [
            'body' => 'hi',
            'headers' => [
                'Accept' => 'application/vnd.ms-excel'
            ]
        ]);

        $this->assertInstanceOf("Psr\Http\Message\ResponseInterface", $response);

        if ($request) {
            $this->assertSame("hi", (string)$request->getBody());
            $this->assertNotEmpty($request->getHeader("Authorization"),
                "Authorization header should still be set, overrides should not remove it");
            $this->assertSame("application/vnd.ms-excel", $request->getHeader("Accept")[0],
                "Accept header should be set to override value");
        } else {
            $this->markTestIncomplete('Mock request handler did not fire');
        }
    }

    public function testGetWithResponseCodeError()
    {
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$mockAssertionFired, &$request) {
            $request = $_request;
            return new Response(500);
        });

        $this->expectException("SoftwarePunt\SoWebapi\WebApiException");
        $this->expectExceptionMessage("Expected 200 OK");

        $client->get('/bla');
    }

    public function testGetWithRequestError()
    {
        $client = new MockClient();

        $this->expectException("SoftwarePunt\SoWebapi\WebApiException");
        $this->expectExceptionMessage("Error in HTTP request");

        $client->get('INVALID URL 😃');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Collections

    public function testProjects()
    {
        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Collections\Projects\ProjectCollection",
            (new Client(new Config()))->projects());
    }

    public function testAppointments()
    {
        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Collections\Appointments\AppointmentCollection",
            (new Client(new Config()))->appointments());
    }

    public function testDocuments()
    {
        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Collections\Appointments\DocumentCollection",
            (new Client(new Config()))->documents());
    }
}