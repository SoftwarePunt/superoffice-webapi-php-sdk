<?php

namespace SoftwarePunt\SoWebApiTests\Collections;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SoftwarePunt\SoWebApi\Client;
use SoftwarePunt\SoWebApi\Collections\Collection;
use SoftwarePunt\SoWebApi\Config;
use SoftwarePunt\SoWebApiTests\Mock\MockClient;

class CollectionTest extends TestCase
{
    public function testQuery()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionTestDummyCollection($cl);

        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Collections\CollectionQuery", $dc->query());
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

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../_samples/projects_all_response.json"));
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
        $this->assertCount(1, $foundItems, 'Query should return one result for sample response');
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

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../_samples/blank_odata_response.json"));
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
        $this->assertCount(0, $foundItems, 'Query should return no results for blank response');
    }
}

class CollectionTestDummyCollection extends Collection
{
    public static string $PATH = "/api/v1/Dummy";
}
