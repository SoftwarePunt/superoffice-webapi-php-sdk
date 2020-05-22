<?php

namespace roydejong\SoWebApiTests\Structs\OData;

use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Structs\OData\ODataPageResponse;

class ODataPageResponseTest extends TestCase
{
    public function testParse()
    {
        $jsonInput = file_get_contents(__DIR__ . "/../../_samples/projects_all_response.json");
        $parsedObj = new ODataPageResponse($jsonInput);

        $this->assertSame("https://sod2.superoffice.com:443/Cust12345/api/v1/Archive//\$metadata", $parsedObj->odata__metadata);
        $this->assertSame(null, $parsedObj->odata__nextLink);
    }
}
