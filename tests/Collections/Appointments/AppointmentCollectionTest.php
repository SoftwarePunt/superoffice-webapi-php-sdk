<?php

namespace SoftwarePunt\SoWebApiTests\Collections\Appointments;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SoftwarePunt\SoWebApiTests\Mock\MockClient;

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
        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\Appointments\ODataAppointmentItem", $allAppointments[0]);
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
                file_get_contents(__DIR__ . "/../../_samples/appointment_3_response.json"));
        });

        $collection = $client->appointments();
        $appointment = $collection->getById(3);

        $this->assertSame("https://mock.superoffice.com/Cust12345/api/v1/Appointment/3", (string)$request->getUri());

        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\Appointments\AppointmentEntity", $appointment,
            "Calling appointments()->testGetById() should return a single AppointmentEntity");

        $this->assertEquals(new \DateTime('2015-11-04T15:56:48'), $appointment->CreatedDate);
        $this->assertSame(3, $appointment->AppointmentId);
        $this->assertSame("blah", $appointment->Description);
        $this->assertEquals(new \DateTime('2015-11-04T13:00:00'), $appointment->StartDate);
        $this->assertEquals(new \DateTime('2015-11-04T14:00:00'), $appointment->EndDate);
    }
}
