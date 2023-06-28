<?php

namespace SoftwarePunt\SoWebApiTests\Structs;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class JsonStructTest extends TestCase
{
    public function testAsJson()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
            public string $name;
            public ?\DateTime $dt;
        };

        $testObj = new $testStruct();
        $testObj->id = 123;
        $testObj->name = "test";
        $testObj->dt = new \DateTime("2019-09-13T10:25:19Z");

        /**
         * @var $testObj JsonStruct
         */

        $expected = '{"id":123,"name":"test","dt":"2019-09-13T10:25:19+00:00"}';
        $actual = $testObj->asJson();
        $this->assertEquals($expected, $actual);
    }

    public function testFromJson()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
            public string $name;
            public ?\DateTime $dt;
        };

        $input = '{"id":123,"name":"test","dt":"2019-09-13T10:25:19+00:00"}';

        /**
         * @var $testObj JsonStruct
         */
        $testObj = new $testStruct($input);

        $this->assertSame(123, $testObj->id);
        $this->assertSame("test", $testObj->name);
        $this->assertSame("2019-09-13T10:25:19+00:00", $testObj->dt->format('c'));
    }

    public function testFromJsonInvalid()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
        };

        $input = '{ not valid json }';

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Could not parse string as JSON');

        /**
         * @var $testObj JsonStruct
         */
        $testObj = new $testStruct($input);
    }

    public function testFromResponse()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
        };

        $response = new Response(200, [], '{"id":123}');
        $fromResponse = $testStruct::fromResponse($response);

        $this->assertSame(123, $fromResponse->id);
    }
}
