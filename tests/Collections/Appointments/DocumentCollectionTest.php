<?php

namespace roydejong\SoWebApiTests\Collections\Appointments;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use roydejong\SoWebApiTests\Mock\MockClient;

class DocumentCollectionTest extends TestCase
{
    public function testQueryAll()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$request) {
            $request = $_request;

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../../_samples/documents_all_response.json"));
        });

        $collection = $client->documents();
        $allDocuments = $collection->query()->execute();

        $this->assertIsArray($allDocuments, 'Query all should return array');
        $this->assertCount(2, $allDocuments, 'Query should return one result for sample response');
        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Appointments\ODataDocumentItem", $allDocuments[0]);
    }

    public function testGetContentStringById()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$request) {
            $request = $_request;

            return new Response(200, [], "oh hi");
        });

        $collection = $client->documents();
        $raw = $collection->getContentStringById(123);

        $this->assertSame("oh hi", $raw);
        $this->assertStringEndsWith("/api/v1/Document/123/Content", (string)$request->getUri());
        $this->assertSame("*", $request->getHeader('Accept')[0]);
    }
}