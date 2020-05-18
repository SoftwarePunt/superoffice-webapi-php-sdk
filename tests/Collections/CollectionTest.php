<?php

namespace roydejong\SoWebApiTests\Collections;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Collections\Collection;
use roydejong\SoWebApi\Config;
use roydejong\SoWebApiTests\Mock\MockClient;

class CollectionTest extends TestCase
{
    public function testQuery()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionTestDummyCollection($cl);

        $this->assertInstanceOf("roydejong\SoWebApi\Collections\CollectionQuery", $dc->query());
    }

    public function testQueryExecute()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $cl = new MockClient();
        $cl->setMockHandler(function (RequestInterface $_request) use (&$mockAssertionFired, &$request) {
            $request = $_request;
            return new Response(200, [], "[]");
        });

        $dc = new CollectionTestDummyCollection($cl);

        $foundItems = $dc->query()
            ->select("Id", "Name")
            ->limit(1234)
            ->execute();

        if ($request) {
            $this->assertSame('https://mock.superoffice.com/Cust12345/api/v1/Dummy?$select=Id,Name&$top=1234',
                (string)$request->getUri());
        } else {
            $this->markTestIncomplete('Mock request handler did not fire');
        }

        $this->assertIsArray($foundItems, "Query execute should always return an array");
        $this->assertEmpty($foundItems, "Query execute should return an empty array in this scenario");
    }

    public function testQueryExecuteBlank()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $cl = new MockClient();
        $cl->setMockHandler(function (RequestInterface $_request) use (&$mockAssertionFired, &$request) {
            $request = $_request;
            return new Response(200, [], "[]");
        });

        $dc = new CollectionTestDummyCollection($cl);
        $foundItems = $dc->query()->execute();

        if ($request) {
            $this->assertSame("https://mock.superoffice.com/Cust12345/api/v1/Dummy",
                (string)$request->getUri());
        } else {
            $this->markTestIncomplete('Mock request handler did not fire');
        }

        $this->assertIsArray($foundItems, "Query execute should always return an array");
        $this->assertEmpty($foundItems, "Query execute should return an empty array in this scenario");
    }
}

class CollectionTestDummyCollection extends Collection
{
    public function getPath(): string
    {
        return "/api/v1/Dummy";
    }
}
