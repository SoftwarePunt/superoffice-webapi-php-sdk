<?php

namespace roydejong\SoWebApi\Structs\Projects;

use roydejong\SoWebApi\Structs\JsonStruct;

class ProjectAssociate extends JsonStruct
{
    public int $AssociateId;
    public string $Name;
    public int $PersonId;
    public int $Rank;
    public string $Tooltip;
    public string $Type;
    public int $GroupIdx;
    public string $FullName;
    public string $FormalName;
    public bool $Deleted;
    public int $EjUserId;
    public string $UserName;
}