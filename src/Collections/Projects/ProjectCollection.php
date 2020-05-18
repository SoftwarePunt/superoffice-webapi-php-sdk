<?php

namespace roydejong\SoWebApi\Collections\Projects;

use roydejong\SoWebApi\Collections\Collection;

class ProjectCollection extends Collection
{
    public function getPath(): string
    {
        return "/api/v1/Project";
    }
}