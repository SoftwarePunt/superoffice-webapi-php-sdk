<?php

namespace roydejong\SoWebApi\Collections;

use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Structs\OData\ODataPageResponse;
use roydejong\SoWebApi\Structs\Struct;
use roydejong\SoWebApi\WebApiException;

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
     * @return array|Struct[] Entities returned in the OData response.
     * @throws WebApiException
     */
    public function executeQuery(CollectionQuery $query): array
    {
        $urlPath = $this->path;
        $queryString = $query->getQueryString();

        if (!empty($queryString)) {
            $urlPath .= "?" . $queryString;
        }

        $response = $this->client->get($urlPath);

        /**
         * @var $pageObj ODataPageResponse
         */
        $pageObj = ODataPageResponse::fromResponse($response);
        return $pageObj->getEntities();
    }
}