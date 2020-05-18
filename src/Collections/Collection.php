<?php

namespace roydejong\SoWebApi\Collections;

use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Structs\Struct;

abstract class Collection
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Begins a query on this collection.
     *
     * @return CollectionQuery
     */
    public function query(): CollectionQuery
    {
        return new CollectionQuery($this);
    }

    /**
     * Executes a query on this collection.
     *
     * @param CollectionQuery $query
     * @return Struct[]
     *
     * @throws
     */
    public function executeQuery(CollectionQuery $query): array
    {
        $urlPath = $this->getPath();
        $queryString = $query->getQueryString();

        if (!empty($queryString)) {
            $urlPath .= "?" . $queryString;
        }

        $response = $this->client->get($urlPath);
        $body = (string)$response->getBody();
        $parsed = json_decode($body, true);

        // TODO Proper response processing into structs

        return $parsed;
    }

    /**
     * Gets the relative path for this collection.
     *
     * @example /api/v1/entity
     * @return string
     */
    public abstract function getPath(): string;
}