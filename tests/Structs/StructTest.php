<?php

namespace SoftwarePunt\SoWebApiTests\Structs;

use PHPUnit\Framework\TestCase;
use SoftwarePunt\SoWebApi\Structs\Struct;

class StructTest extends TestCase
{
    public function testAsArray()
    {
        $dt = new \DateTime("2019-09-13T10:25:19Z");

        $subStruct = new StructTestTestSubStruct();
        $subStruct->blah = "sub_value";

        $testObj = new StructTestTestStruct();
        $testObj->id = 123;
        $testObj->name = "test";
        $testObj->dt = $dt;
        $testObj->untyped = "blah";
        $testObj->sub = $subStruct;

        StructTestTestStruct::$ignore = "ignore";

        /**
         * @var $testObj Struct
         */

        $expected = [
            'id' => 123,
            'name' => "test",
            'dt' => $dt->format('c'), // DateTime should be auto-formatted
            'untyped' => "blah",
            'sub' => $subStruct->asArray()
        ];
        $actual = $testObj->asArray();
        $this->assertEquals($expected, $actual);
    }

    public function testFromArray()
    {
        $dt = new \DateTime("2019-09-13T10:25:19+00:00");

        $input = [
            'id' => 123,
            'name' => "test",
            'dt' => $dt,
            'untyped' => "blah",
            'sub' => ['blah' => 'sub_value']
        ];

        StructTestTestStruct::$ignore = "ignore";

        $testObj = new StructTestTestStruct($input);

        $this->assertSame(123, $testObj->id);
        $this->assertSame("test", $testObj->name);
        $this->assertSame($dt, $testObj->dt);
        $this->assertSame("blah", $testObj->untyped);

        $subStructExpected = new StructTestTestSubStruct();
        $subStructExpected->blah = "sub_value";

        $this->assertEquals($subStructExpected, $testObj->sub);
    }
}

class StructTestTestStruct extends Struct {
    public static string $ignore;

    public int $id;
    public string $name;
    public ?\DateTime $dt;
    public $untyped;
    public StructTestTestSubStruct $sub;
};

class StructTestTestSubStruct extends Struct {
    public string $blah;
};