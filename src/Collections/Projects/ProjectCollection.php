<?php

namespace roydejong\SoWebApi\Collections\Projects;

use roydejong\SoWebApi\Collections\Collection;
use roydejong\SoWebApi\Structs\JsonStruct;
use roydejong\SoWebApi\Structs\Projects\ProjectEntity;
use roydejong\SoWebApi\WebApiException;

class ProjectCollection extends Collection
{
    public static string $PATH = "/api/v1/Project";

    /**
     * Gets a stub project entity with default values.
     *
     * Set default values into a new ProjectEntity.
     * NetServer calculates default values on the entity, which is required when creating/storing a new instance.
     *
     * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1ProjectEntity_DefaultProjectEntity.htm
     *
     * @return ProjectEntity|JsonStruct
     * @throws WebApiException
     */
    public function getDefault(): ProjectEntity
    {
        return ProjectEntity::fromResponse(
            $this->client->get(self::$PATH . "/default")
        );
    }

    /**
     * Gets a ProjectEntity object by id.
     *
     * @see https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/v1ProjectEntity_GetProjectEntity.htm
     *
     * @param int $id The id of the ProjectEntity to return.
     * @return ProjectEntity|JsonStruct
     * @throws WebApiException
     */
    public function getById(int $id): ProjectEntity
    {
        return ProjectEntity::fromResponse(
            $this->client->get(self::$PATH . "/{$id}")
        );
    }
}