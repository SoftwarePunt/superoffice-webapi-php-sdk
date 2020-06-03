<?php

namespace roydejong\SoWebApiTests\Http;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Http\BinaryResponse;

class BinaryResponseTest extends TestCase
{
    public function testGetContentType()
    {
        $binaryResponse = new BinaryResponse(
            new Response(200, ['Content-Type' => 'text/plain'], "test 123")
        );

        $this->assertSame("text/plain", $binaryResponse->getContentType());
    }

    public function testGetContent()
    {
        $binaryResponse = new BinaryResponse(
            new Response(200, ['Content-Type' => 'text/plain'], "test 123")
        );

        $this->assertSame("test 123", $binaryResponse->getContentAsString());
        $this->assertSame("test 123", (string)$binaryResponse->getContentAsStream());

        $stream = $binaryResponse->getContentAsStream();
        $stream->rewind();

        $this->assertSame("test 123", $stream->read($stream->getSize()));
    }
}