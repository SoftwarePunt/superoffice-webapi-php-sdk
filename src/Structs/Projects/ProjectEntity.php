<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\JsonStruct;

class ProjectEntity extends JsonStruct
{
    public int $ProjectId;
    public string $Name;
    public string $ProjectNumber;
    public \DateTime $CreatedDate;
    public \DateTime $UpdatedDate;
    public string $Description;
    public string $Postit;
    public ProjectType $ProjectType;
}