<?php

namespace roydejong\SoWebApiTests\Structs\OData;

use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Structs\OData\ODataPageResponse;
use roydejong\SoWebApi\Structs\Projects\ODataProjectItem;

class ODataPageResponseTest extends TestCase
{
    public function testParseBaseProperties()
    {
        $jsonInput = file_get_contents(__DIR__ . "/../../_samples/projects_all_response.json");
        $parsedObj = new ODataPageResponse($jsonInput);

        $this->assertSame("https://sod2.superoffice.com:443/Cust12345/api/v1/Archive//\$metadata", $parsedObj->odata__metadata);
        $this->assertSame(null, $parsedObj->odata__nextLink);
        $this->assertIsArray($parsedObj->value);
        $this->assertIsArray($parsedObj->value[0]);
    }

    /**
     * @depends testParseBaseProperties
     */
    public function testParseEntities()
    {
        $jsonInput = file_get_contents(__DIR__ . "/../../_samples/projects_all_response.json");
        $parsedObj = new ODataPageResponse($jsonInput);

        /**
         * @var $entities ODataProjectItem[]
         */
        $entities = $parsedObj->getEntities();

        $this->assertIsArray($entities);
        $this->assertNotEmpty($entities);

        $projEntry = $entities[0];

        $this->assertInstanceOf("roydejong\SoWebApi\Structs\Projects\ODataProjectItem", $projEntry);

        $this->assertSame(1, $projEntry->projectId);
        $this->assertSame("Example: Internal project", $projEntry->name);
        $this->assertSame("Example: Internal project", $projEntry->text);
        $this->assertSame("10011", $projEntry->number);
        $this->assertSame("2020-04-22", $projEntry->updatedDate->format('Y-m-d'));
    }

    public function testParseWithBadEntityName()
    {
        $jsonInput = file_get_contents(__DIR__ . "/../../_samples/unsupported_odata_response.json");
        $parsedObj = new ODataPageResponse($jsonInput);

        $this->expectException("roydejong\SoWebapi\WebApiException");
        $this->expectExceptionMessage("unsupported entity name: unsupported-entity");

        $parsedObj->getEntities();
    }
}
