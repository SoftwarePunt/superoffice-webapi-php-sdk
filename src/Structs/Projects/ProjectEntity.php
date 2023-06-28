<?php

namespace SoftwarePunt\SoWebApi\Structs\Projects;

use SoftwarePunt\SoWebApi\Structs\UDef\UDefJsonStruct;

class ProjectEntity extends UDefJsonStruct
{
    public int $ProjectId;
    public string $Name;
    public string $ProjectNumber;
    public \DateTime $CreatedDate;
    public \DateTime $UpdatedDate;
    public string $Description;
    public string $Postit;
    public ?ProjectAssociate $CreatedBy;
    public ProjectAssociate $UpdatedBy;
    public ProjectAssociate $Associate;
    public ProjectStatus $ProjectStatus;
    public ProjectType $ProjectType;
    public bool $HasImage;
    public string $ImageDescription;
    public int $ActiveStatusMonitorId;
    public int $ActiveLinks;
    public bool $Completed;
    public \DateTime $NextMilestoneDate;
    public int $NmdAppointmentId;
    public \DateTime $EndDate;
    public int $ActiveErpLinks;
    public array $UserDefinedFields;
    public array $ExtraFields;
    public array $CustomFields;
    public \DateTime $PublishEventDate;
    public \DateTime $PublishTo;
    public \DateTime $PublishFrom;
    public bool $IsPublished;

    /**
     * @var ProjectUrl[]
     */
    public array $Urls;

    /**
     * @var ProjectLink[]
     */
    public array $Links;

    /**
     * @inheritDoc
     */
    public function assignProperty(string $propName, $value): void
    {
        if ($propName === "Links" && is_array($value)) {
            $this->Links = [];

            foreach ($value as $linkEntry) {
                $link = new ProjectLink();
                $link->fillFromArray($linkEntry);

                $this->Links[] = $link;
            }
        } else if ($propName === "Urls" && is_array($value)) {
            $this->Urls = [];

            foreach ($value as $urlEntry) {
                $url = new ProjectUrl();
                $url->fillFromArray($urlEntry);

                $this->Urls[] = $url;
            }
        } else {
            parent::assignProperty($propName, $value);
        }
    }
}