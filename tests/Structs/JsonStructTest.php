<?php

namespace roydejong\SoWebApiTests\Structs;

use roydejong\SoWebApi\Structs\JsonStruct;
use PHPUnit\Framework\TestCase;

class JsonStructTest extends TestCase
{
    public function testAsArray()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
            public string $name;
        };

        $testObj = new $testStruct();
        $testObj->id = 123;
        $testObj->name = "test";

        /**
         * @var $testObj JsonStruct
         */

        $expected = [
            'id' => 123,
            'name' => "test"
        ];
        $actual = $testObj->asArray();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @depends testAsArray
     */
    public function testAsJson()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
            public string $name;
        };

        $testObj = new $testStruct();
        $testObj->id = 123;
        $testObj->name = "test";

        /**
         * @var $testObj JsonStruct
         */

        $expected = '{"id":123,"name":"test"}';
        $actual = $testObj->asJson();
        $this->assertEquals($expected, $actual);
    }

    public function testFromJson()
    {
        $testStruct = new class extends JsonStruct {
            public int $id;
            public string $name;
        };

        $input = '{"id":123,"name":"test"}';

        /**
         * @var $testObj JsonStruct
         */
        $testObj = new $testStruct($input);

        $this->assertSame(123, $testObj->id);
        $this->assertSame("test", $testObj->name);
    }
}
