<?php

namespace roydejong\SoWebApi\Structs\UDef;

use roydejong\SoWebApi\Structs\JsonStruct;

class UDefListValue extends JsonStruct
{
    public int $Id;
    public string $DisplayText;
    public string $DisplayTooltip;
}