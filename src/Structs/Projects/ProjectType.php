<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\Struct;

class ProjectType extends Struct
{
    public int $Id;
    public string $Value;
    public string $Tooltip;
}