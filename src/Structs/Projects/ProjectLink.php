<?php

namespace SoftwarePunt\SoWebApi\Structs\Projects;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class ProjectLink extends JsonStruct
{
    public string $EntityName;
    public int $Id;
    public string $Description;
    public string $ExtraInfo;
    public int $LinkId;
}