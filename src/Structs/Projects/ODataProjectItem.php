<?php

namespace SoftwarePunt\SoWebApi\Structs\Projects;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class ODataProjectItem extends JsonStruct
{
    public int $projectId;
    public string $name;
    public string $text;
    public string $number;
    public string $associateId;
    public string $type;
    public string $status;
    public bool $hasGuide;
    public \DateTime $nextMilestone;
    public \DateTime $endDate;
    public string $imageThumbnail;
    public bool $activeErpLinks;
    public string $registeredBy;
    public \DateTime $registeredDate;
    public bool $hasInfoText;
    public string $description;
    public bool $completed;
    public string $icon;
    public string $updatedBy;
    public \DateTime $updatedDate;
}