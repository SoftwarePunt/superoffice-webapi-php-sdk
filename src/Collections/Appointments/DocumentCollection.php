<?php

namespace SoftwarePunt\SoWebApi\Collections\Appointments;

use SoftwarePunt\SoWebApi\Collections\Collection;
use SoftwarePunt\SoWebApi\Http\BinaryResponse;

class DocumentCollection extends Collection
{
    public static string $PATH = "/api/v1/Document";

    /**
     * Downloads and returns the document content.
     *
     * @param int $documentId
     * @return BinaryResponse
     */
    public function getContentById(int $documentId): BinaryResponse
    {
        $response = $this->client->get(self::$PATH . "/{$documentId}/Content", [
            'headers' => [
                'Accept' => "*/*"
            ]
        ]);
        return new BinaryResponse($response);
    }
}