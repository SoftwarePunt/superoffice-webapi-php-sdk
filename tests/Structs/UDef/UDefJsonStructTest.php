<?php

namespace SoftwarePunt\SoWebApiTests\Structs\UDef;

use PHPUnit\Framework\TestCase;
use SoftwarePunt\SoWebApi\Structs\JsonStruct;
use SoftwarePunt\SoWebApi\Structs\UDef\UDefJsonStruct;

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
            "SuperOffice:4" => "invalid",
            "SuperOffice:5" => "[I:1234]"
        ];

        $this->assertSame(0, $struct->getUserInt("invalid_key"));
        $this->assertSame(123, $struct->getUserInt("SuperOffice:1"));
        $this->assertSame(123, $struct->getUserInt("SuperOffice:2"));
        $this->assertSame(0, $struct->getUserInt("SuperOffice:3"));
        $this->assertSame(0, $struct->getUserInt("SuperOffice:4"));
        $this->assertSame(1234, $struct->getUserInt("SuperOffice:5"));
    }

    public function testGetUserIntNullable()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "SuperOffice:1" => "123",
            "SuperOffice:2" => "123.45",
            "SuperOffice:3" => "0",
            "SuperOffice:4" => "invalid",
            "SuperOffice:5" => "[I:1234]"
        ];

        $this->assertSame(null, $struct->getUserIntNullable("invalid_key"));
        $this->assertSame(123, $struct->getUserIntNullable("SuperOffice:1"));
        $this->assertSame(123, $struct->getUserIntNullable("SuperOffice:2"));
        $this->assertSame(0, $struct->getUserIntNullable("SuperOffice:3"));
        $this->assertSame(null, $struct->getUserIntNullable("SuperOffice:4"));
        $this->assertSame(1234, $struct->getUserIntNullable("SuperOffice:5"));
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
            "SuperOffice:1" => "[D:11/30/2011]",
            "SuperOffice:2" => "2015-04-03 11:22:33",
            "SuperOffice:3" => "",
            "SuperOffice:4" => "invalid date/time",
            "SuperOffice:5" => "[D:invalid_format]",
            "SuperOffice:6" => "[D:01/01/0001 00:00:00.0000000]"
        ];

        $this->assertNull($struct->getUserDate("invalid_key"));
        $this->assertSame("2011-11-30", $struct->getUserDate("SuperOffice:1")->format('Y-m-d'));
        $this->assertSame("2015-04-03 11:22:33", $struct->getUserDate("SuperOffice:2")->format('Y-m-d H:i:s'));
        $this->assertNull($struct->getUserDate("SuperOffice:3"));
        $this->assertNull($struct->getUserDate("SuperOffice:4"));
        $this->assertNull($struct->getUserDate("SuperOffice:5"));
        $this->assertSame("0001-01-01 00:00:00", $struct->getUserDate("SuperOffice:6")->format('Y-m-d H:i:s'));
    }

    public function testGetUserListValue()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "function-1" => "[I:11]",
            "function-1:DisplayText" => "func name",
            "function-1:DisplayTooltip" => "the tip",

            "function-2" => "not-a-list-ref",

            "function-3" => "[I:INVALID]"
        ];

        $function1 = $struct->getUserListValue("function-1");
        $this->assertInstanceOf("SoftwarePunt\SoWebApi\Structs\UDef\UDefListValue", $function1);
        $this->assertSame(11, $function1->Id);
        $this->assertSame("func name", $function1->DisplayText);
        $this->assertSame("the tip", $function1->DisplayTooltip);

        $this->assertNull($struct->getUserListValue("function-2"));

        $this->assertNull($struct->getUserListValue("function-3"));
    }

    public function testGetStringWithListResolve()
    {
        $struct = new UDefJsonStructTestStruct();

        $struct->UserDefinedFields = [
            "function-1" => "[I:11]",
            "function-1:DisplayText" => "func name",
            "function-1:DisplayTooltip" => "the tip",

            "function-2" => "not-a-list-ref",
            "function-2:DisplayText" => "ignored",

            "function-3" => "[I:INVALID]",
            "function-3:DisplayText" => "",
        ];

        $this->assertSame(null, $struct->getUserString("invalid-key", true));
        $this->assertSame("func name", $struct->getUserString("function-1", true));
        $this->assertSame("not-a-list-ref", $struct->getUserString("function-2", true));
        $this->assertSame("", $struct->getUserString("function-3", true));
    }
}

class UDefJsonStructTestStruct extends UDefJsonStruct { };