<?php

namespace roydejong\SoWebApiTests\Structs;

use roydejong\SoWebApi\Structs\JsonStruct;
use roydejong\SoWebApi\Structs\Struct;
use PHPUnit\Framework\TestCase;

class StructTest extends TestCase
{
    public function testAsArray()
    {
        $testStruct = new class extends Struct {
            public int $id;
            public string $name;
            public ?\DateTime $dt;
        };

        $dt = new \DateTime("2019-09-13T10:25:19Z");

        $testObj = new $testStruct();
        $testObj->id = 123;
        $testObj->name = "test";
        $testObj->dt = $dt;

        /**
         * @var $testObj Struct
         */

        $expected = [
            'id' => 123,
            'name' => "test",
            'dt' => $dt->format('c') // DateTime should be auto-formatted
        ];
        $actual = $testObj->asArray();
        $this->assertEquals($expected, $actual);
    }

    public function testFromArray()
    {
        $testStruct = new class extends Struct {
            public int $id;
            public string $name;
            public ?\DateTime $dt;
        };

        $dt = new \DateTime("2019-09-13T10:25:19+00:00");

        $input = [
            'id' => 123,
            'name' => "test",
            'dt' => $dt
        ];

        /**
         * @var $testObj JsonStruct
         */
        $testObj = new $testStruct($input);

        $this->assertSame(123, $testObj->id);
        $this->assertSame("test", $testObj->name);
        $this->assertSame($dt, $testObj->dt);
    }
}
