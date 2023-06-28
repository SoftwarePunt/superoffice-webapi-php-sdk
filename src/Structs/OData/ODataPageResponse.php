<?php

namespace SoftwarePunt\SoWebApi\Structs\OData;

use SoftwarePunt\SoWebApi\Structs\Appointments\ODataAppointmentItem;
use SoftwarePunt\SoWebApi\Structs\Appointments\ODataDocumentItem;
use SoftwarePunt\SoWebApi\Structs\JsonStruct;
use SoftwarePunt\SoWebApi\Structs\Projects\ODataProjectItem;
use SoftwarePunt\SoWebApi\WebApiException;

class ODataPageResponse extends JsonStruct
{
    public string $odata__metadata;
    public ?string $odata__nextLink;
    public array $value;

    /**
     * @return array|JsonStruct[]
     */
    public function getEntities(): array
    {
        $entities = [];

        foreach ($this->value as $item) {
            $entityName = $item['EntityName'];
            $entity = null;

            switch ($entityName) {
                case "project":
                    $entity = new ODataProjectItem();
                    break;
                case "appointment":
                    $entity = new ODataAppointmentItem();
                    break;
                case "document":
                    $entity = new ODataDocumentItem();
                    break;
                default:
                    throw new WebApiException("OData response contains unsupported entity name: {$entityName}");
            }

            $entity->fillFromArray($item);
            $entities[] = $entity;
        }

        return $entities;
    }
}