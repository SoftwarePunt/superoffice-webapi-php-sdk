<?php

namespace roydejong\SoWebApiTests\Collections\Projects;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use roydejong\SoWebApiTests\Mock\MockClient;

class ProjectCollectionTest extends TestCase
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
                file_get_contents(__DIR__ . "/../../_samples/projects_all_response.json"));
        });

        $collection = $client->projects();
        $allProjects = $collection->query()->execute();

        $this->assertIsArray($allProjects, 'Query all should return array');
        $this->assertCount(1, $allProjects, 'Query should return one result for sample response');
        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Projects\ODataProjectItem", $allProjects[0]);
    }

    public function testGetDefault()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$request) {
            $request = $_request;

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../../_samples/project_default_response.json"));
        });

        $collection = $client->projects();
        $defaultProject = $collection->getDefault();

        $this->assertSame("https://mock.superoffice.com/Cust12345/api/v1/Project/default", (string)$request->getUri());

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Projects\ProjectEntity", $defaultProject,
            "Calling products()->getDefault() should return a single project entity");

        $this->assertSame(0, $defaultProject->ProjectId);
        $this->assertSame("10015", $defaultProject->ProjectNumber);
        $this->assertSame("Conference", $defaultProject->ProjectType->Value);
    }

    public function testGetById()
    {
        /**
         * @var $request RequestInterface
         */
        $request = null;

        $client = new MockClient();
        $client->setMockHandler(function (RequestInterface $_request) use (&$request) {
            $request = $_request;

            return new Response(200, [],
                file_get_contents(__DIR__ . "/../../_samples/project_1_response.json"));
        });

        $collection = $client->projects();
        $defaultProject = $collection->getById(1);

        $this->assertSame("https://mock.superoffice.com/Cust12345/api/v1/Project/1", (string)$request->getUri());

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Projects\ProjectEntity", $defaultProject,
            "Calling products()->getDefault() should return a single project entity");

        $this->assertSame(1, $defaultProject->ProjectId);
        $this->assertSame("Example: Internal project", $defaultProject->Name);
        $this->assertSame("10011", $defaultProject->ProjectNumber);
    }
}