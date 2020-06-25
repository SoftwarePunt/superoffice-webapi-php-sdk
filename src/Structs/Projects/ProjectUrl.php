<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\JsonStruct;

class ProjectUrl extends JsonStruct
{
    public string $Value;
    public string $StrippedValue;
    public string $Description;
}