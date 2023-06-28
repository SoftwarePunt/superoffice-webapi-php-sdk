<?php

namespace SoftwarePunt\SoWebApi\Structs\UDef;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class UDefListValue extends JsonStruct
{
    public int $Id;
    public string $DisplayText;
    public string $DisplayTooltip;
}