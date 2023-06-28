<?php

namespace SoftwarePunt\SoWebApi\Structs\Projects;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class ProjectStatus extends JsonStruct
{
    public int $Id;
    public string $Value;
    public string $Tooltip;
}