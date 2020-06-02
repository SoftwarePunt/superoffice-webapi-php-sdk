<?php

namespace roydejong\SoWebApiTests\Structs;

use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Structs\UDefJsonStruct;

class UDefJsonStructTest extends TestCase
{
    public function testGetUserString()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "SuperOffice:1" => "Blah",
            "SuperOffice:2" => ""
        ];

        $this->assertNull($struct->getUserString("invalid_key"));
        $this->assertEquals("Blah", $struct->getUserString("SuperOffice:1"));
        $this->assertEquals("", $struct->getUserString("SuperOffice:2"));
        $this->assertEquals(null, $struct->getUserString("SuperOffice:INVALID"));
    }

    public function testGetUserInt()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "SuperOffice:1" => "123",
            "SuperOffice:2" => "123.45",
            "SuperOffice:3" => "0",
            "SuperOffice:4" => "invalid"
        ];

        $this->assertSame(0, $struct->getUserInt("invalid_key"));
        $this->assertSame(123, $struct->getUserInt("SuperOffice:1"));
        $this->assertSame(123, $struct->getUserInt("SuperOffice:2"));
        $this->assertSame(0, $struct->getUserInt("SuperOffice:3"));
        $this->assertSame(0, $struct->getUserInt("SuperOffice:4"));
    }

    public function testGetUserBool()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "SuperOffice:1" => "True",
            "SuperOffice:2" => "False",
            "SuperOffice:3" => "1",
            "SuperOffice:4" => "0",
            "SuperOffice:5" => "asdf",
            "SuperOffice:6" => ""
        ];

        $this->assertFalse($struct->getUserBool("invalid_key"));
        $this->assertTrue($struct->getUserBool("SuperOffice:1"));
        $this->assertFalse($struct->getUserBool("SuperOffice:2"));
        $this->assertTrue($struct->getUserBool("SuperOffice:3"));
        $this->assertFalse($struct->getUserBool("SuperOffice:4"));
        $this->assertFalse($struct->getUserBool("SuperOffice:5"));
        $this->assertFalse($struct->getUserBool("SuperOffice:6"));
    }

    public function testGetUserDate()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "SuperOffice:1" => "[D:01/02/2011]",
            "SuperOffice:2" => "2015-04-03 11:22:33",
            "SuperOffice:3" => "",
            "SuperOffice:4" => "invalid date/time",
        ];

        $this->assertNull($struct->getUserDate("invalid_key"));
        $this->assertSame("2011-02-01", $struct->getUserDate("SuperOffice:1")->format('Y-m-d'));
        $this->assertSame("2015-04-03 11:22:33", $struct->getUserDate("SuperOffice:2")->format('Y-m-d H:i:s'));
        $this->assertNull($struct->getUserDate("SuperOffice:3"));
        $this->assertNull($struct->getUserDate("SuperOffice:4"));
    }
}

class UDefJsonStructTestStruct extends UDefJsonStruct { };