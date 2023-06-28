<?php

namespace SoftwarePunt\SoWebApi\Structs\Projects;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class ProjectUrl extends JsonStruct
{
    public string $Value;
    public string $StrippedValue;
    public string $Description;
}