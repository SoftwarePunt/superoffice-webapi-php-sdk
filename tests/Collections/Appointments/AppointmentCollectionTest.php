<?php

namespace roydejong\SoWebApiTests\Collections\Appointments;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use roydejong\SoWebApiTests\Mock\MockClient;

class AppointmentCollectionTest extends TestCase
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
                file_get_contents(__DIR__ . "/../../_samples/appointments_all_response.json"));
        });

        $collection = $client->appointments();
        $allAppointments = $collection->query()->execute();

        $this->assertIsArray($allAppointments, 'Query all should return array');
        $this->assertCount(3, $allAppointments, 'Query should return one result for sample response');
        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Appointments\ODataAppointmentItem", $allAppointments[0]);
    }
}
