<?php

namespace roydejong\SoWebApiTests\Collections\Projects;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use roydejong\SoWebApiTests\Mock\MockClient;

class ProjectCollectionTest extends TestCase
{
    public function testGetDefault()
    {
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$request) {
            $request = $_request;

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../../_samples/project_default_response.json"));
        });

        $collection = $client->projects();
        $defaultProject = $collection->getDefault();

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Projects\ProjectEntity", $defaultProject,
            "Calling products()->getDefault() should return a single project entity");

        $this->assertSame(0, $defaultProject->ProjectId);
        $this->assertSame("10015", $defaultProject->ProjectNumber);
        $this->assertSame("Conference", $defaultProject->ProjectType->Value);
    }
}
