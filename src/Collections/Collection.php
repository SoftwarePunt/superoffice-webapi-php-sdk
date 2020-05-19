<?php

namespace roydejong\SoWebApi\Collections;

use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Structs\Struct;

abstract class Collection
{
    protected string $path;
    protected Client $client;

    final public function __construct(Client $client)
    {
        $this->path = static::$PATH;
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
        $urlPath = $this->path;
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
}