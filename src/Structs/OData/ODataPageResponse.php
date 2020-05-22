<?php

namespace roydejong\SoWebApi\Structs\OData;

use roydejong\SoWebApi\Structs\JsonStruct;

class ODataPageResponse extends JsonStruct
{
    public string $odata__metadata;
    public ?string $odata__nextLink;
    public array $value;
}