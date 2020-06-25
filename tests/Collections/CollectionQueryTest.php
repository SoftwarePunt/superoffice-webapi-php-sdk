<?php

namespace roydejong\SoWebApiTests\Collections;

use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Collections\Collection;
use roydejong\SoWebApi\Collections\CollectionQuery;
use roydejong\SoWebApi\Config;

class CollectionQueryTest extends TestCase
{
    public function testEmpty()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        $this->assertEmpty($cq->getQueryParams(),
            'An empty CollectionQuery should produce no query params');
    }

    public function testSelect()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        // Scenario 1: a single column
        $this->assertSame($cq, $cq->select("SomeCol"));
        $this->assertSame(['$select' => "SomeCol"], $cq->getQueryParams());

        // Scenario 2: no columns
        $this->assertSame($cq, $cq->select());
        $this->assertSame([], $cq->getQueryParams(),
            "Calling select() with no values should clear selection criteria");

        // Scenario 3: multi columns
        $this->assertSame($cq, $cq->select("Abc", "Def", "Ghi"));
        $this->assertSame(['$select' => "Abc,Def,Ghi"], $cq->getQueryParams(),
            "Calling select() with multiple values should be be imploded in the query");

        // Scenario 4: wildcard
        $this->assertSame($cq, $cq->select("*"));
        $this->assertSame([], $cq->getQueryParams(),
            "Calling select() with a single wildcard \"*\" value should clear selection criteria");
    }

    public function testLimit()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        $this->assertSame($cq, $cq->limit(123));
        $this->assertSame(['$top' => 123], $cq->getQueryParams());
    }

    public function testOffset()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        $this->assertSame($cq, $cq->offset(456));
        $this->assertSame(['$skip' => 456], $cq->getQueryParams());
    }

    /**
     * @depends testSelect
     * @depends testLimit
     * @depends testOffset
     */
    public function testGetQueryString()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        $cq->select('Id,Name');
        $cq->limit(123);
        $cq->offset(456);

        $this->assertSame(
            '$select=Id,Name&$top=123&$skip=456',
            $cq->getQueryString(),
            "Query string should be generated without encoding"
        );
    }

    /**
     * @depends testSelect
     * @depends testLimit
     * @depends testOffset
     */
    public function testFilter()
    {
        $cl = new Client(new Config([]));
        $dc = new CollectionQueryTestDummyCollection($cl);
        $cq = new CollectionQuery($dc);

        $cq->andWhereEquals('name', "bob");
        $cq->andWhereEquals('enabled', true);
        $cq->andWhereEquals('type', [1,2,3]);

        $this->assertSame("\$filter=name eq 'bob' and enabled eq 1 and type oneOf('1','2','3')",
            $cq->getQueryString()
        );
    }
}

class CollectionQueryTestDummyCollection extends Collection
{
    public static string $PATH = "/api/v1/Dummy";
}
