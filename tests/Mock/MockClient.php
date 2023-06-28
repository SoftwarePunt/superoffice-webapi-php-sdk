<?php

namespace SoftwarePunt\SoWebApiTests\Mock;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SoftwarePunt\SoWebApi\Client;
use SoftwarePunt\SoWebApi\Config;

class MockClient extends Client
{
    public function __construct(?Config $config = null)
    {
        if (!$config) {
            $config = new Config();
            $config->environment = "mock";
            $config->tenantId = "Cust12345";
        }

        parent::__construct($config);
    }

    public function setMockResponse(Response $response): void
    {
        $this->setMockHandler(new MockHandler([$response]));
    }

    public function setMockHandler(callable $handler): void
    {
        $this->httpClient = new \GuzzleHttp\Client([
            'handler' => $handler
        ]);
    }
}