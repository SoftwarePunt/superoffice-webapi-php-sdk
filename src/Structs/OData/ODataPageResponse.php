<?php

namespace roydejong\SoWebApi\Structs\OData;

use roydejong\SoWebApi\Structs\JsonStruct;
use roydejong\SoWebApi\Structs\Projects\ODataProjectItem;
use roydejong\SoWebApi\WebApiException;

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
                default:
                    throw new WebApiException("OData response contains unsupported entity name: {$entityName}");
            }

            $entity->fillFromArray($item);
            $entities[] = $entity;
        }

        return $entities;
    }
}