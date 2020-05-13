<?php

namespace roydejong\SoWebApiTests;

use roydejong\SoWebApi\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $config = new Config();

        $this->assertEmpty((array)$config, "Empty config constructor should result in an empty object");
    }

    public function testConstructorWithValues()
    {
        $inputArr = [
            'tenantId' => 'Cust00000'
        ];

        $config = new Config($inputArr);

        $this->assertEquals($inputArr, (array)$config, "Constructor with values should set them on the object");
    }

    public function testConstructorWithBadKey()
    {
        $inputArr = [
            'badKey' => 'http://so.test/'
        ];

        $this->expectException("\InvalidArgumentException");
        new Config($inputArr);
    }
}
