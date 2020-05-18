<?php

namespace roydejong\SoWebApi\Collections;

use roydejong\SoWebApi\Client;

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
     */
    public function executeQuery(CollectionQuery $query)
    {
        // TODO
    }

    /**
     * Gets the relative path for this collection.
     *
     * @example /api/v1/entity
     * @return string
     */
    public abstract function getPath(): string;
}