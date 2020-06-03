<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\JsonStruct;

class ProjectLink extends JsonStruct
{
    public string $EntityName;
    public int $Id;
    public string $Description;
    public string $ExtraInfo;
    public int $LinkId;
}