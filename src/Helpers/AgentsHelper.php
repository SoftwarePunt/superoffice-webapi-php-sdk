<?php

namespace SoftwarePunt\SoWebApi\Helpers;

use SoftwarePunt\SoWebApi\Client;

final class AgentsHelper
{
    public function __construct(public readonly Client $client)
    {
    }

    /**
     * Sends a POST request to `/api/v1/Agents/BLOB/GetProjectImage`.
     *
     * @return string Base64-encoded image blob
     */
    public function getProjectImageBlob(int $projectId): string
    {
        $response = $this->client->post(
            path: "/api/v1/Agents/BLOB/GetProjectImage",
            body: [
                'ProjectId' => $projectId
            ]
        );
        return $response->getBody()->getContents();
    }
}