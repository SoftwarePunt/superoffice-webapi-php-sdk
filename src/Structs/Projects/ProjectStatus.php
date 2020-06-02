<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\JsonStruct;

class ProjectStatus extends JsonStruct
{
    public int $Id;
    public string $Value;
    public string $Tooltip;
}