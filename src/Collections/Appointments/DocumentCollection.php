<?php

namespace roydejong\SoWebApi\Collections\Appointments;

use roydejong\SoWebApi\Collections\Collection;

class DocumentCollection extends Collection
{
    public static string $PATH = "/api/v1/Document";

    /**
     * Streams the document content into a string value.
     *
     * @param int $documentId
     * @return string
     */
    public function getContentStringById(int $documentId): string
    {
        return $this->client->get(self::$PATH . "/{$documentId}/Content", [
            'headers' => [
                'Accept' => "*"
            ]
        ])->getBody();
    }
}