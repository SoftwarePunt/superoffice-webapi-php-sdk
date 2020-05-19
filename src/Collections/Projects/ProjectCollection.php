<?php

namespace roydejong\SoWebApi\Collections\Projects;

use roydejong\SoWebApi\Collections\Collection;
use roydejong\SoWebApi\Structs\Projects\ProjectEntity;

class ProjectCollection extends Collection
{
    public static string $PATH = "/api/v1/Project";

    public function getDefault(): ProjectEntity
    {
        $response = $this->client->get(self::$PATH . "/default");
        return new ProjectEntity((string)$response->getBody());
    }
}