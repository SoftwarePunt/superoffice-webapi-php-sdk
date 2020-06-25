<?php

namespace roydejong\SoWebApi\Collections;

use roydejong\SoWebApi\Structs\Struct;
use roydejong\SoWebApi\WebApiException;

class CollectionQuery
{
    protected Collection $collection;

    protected array $select;
    protected string $filter;
    protected int $limit;
    protected int $offset;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;

        $this->select = [];
        $this->filter = "";
        $this->limit = 0;
        $this->offset = 0;
    }

    /**
     * Sets the list of columns  limit ($top).
     *
     * @param string|string[] $columns Max amount of records to return.
     * @return $this
     */
    public function select(string ...$columns): self
    {
        $columnCount = count($columns);

        if ($columnCount === 0) {
            // No columns in input -> clear.
            $this->select = [];
            return $this;
        }

        if ($columnCount === 1) {
            // One column: check value
            $oneValue = $columns[0];

            if ($oneValue === "*") {
                // Looks like a wildcard input, e.g. $query->select("*") -> clear.
                $this->select = [];
                return $this;
            }
        }

        // Just a regular list of column names it seems
        $this->select = $columns;
        return $this;
    }

    /**
     * Adds a filter condition with an "and" clause.
     *
     * @param string $columnName
     * @param string $operator
     * @param bool|int|float|string|array $expectedValue
     * @return $this
     */
    protected function andWhere(string $columnName, $operator, $expectedValue): self
    {
        if (!empty($this->filter)) {
            $this->filter .= " and ";
        }

        if (is_bool($expectedValue)) {
            $expectedValue = $expectedValue ? 1 : 0;
        }

        if (is_integer($expectedValue) || is_float($expectedValue)) {
            $this->filter .= "{$columnName} {$operator} {$expectedValue}";
        } else if (is_string($expectedValue)) {
            $this->filter .= "{$columnName} {$operator} '{$expectedValue}'";
        } else if (($operator === "eq" || $operator === "equals" || $operator === "oneOf") && is_array($expectedValue)) {
            $oneOfList = "'" . implode("','", $expectedValue) . "'";
            $this->filter .= "{$columnName} oneOf({$oneOfList})";
        }

        return $this;
    }

    /**
     * Adds a filter condition with an "and equals" clause.
     *
     * @param string $columnName
     * @param bool|int|float|string|array $expectedValue
     * @return $this
     */
    public function andWhereEquals(string $columnName, $expectedValue): self
    {
        return $this->andWhere($columnName, "eq", $expectedValue);
    }

    /**
     * Adds a filter condition with an "and greater than" clause.
     *
     * @param string $columnName
     * @param int|float $expectedValue
     * @return $this
     */
    public function andWhereGreaterThan(string $columnName, $expectedValue): self
    {
        return $this->andWhere($columnName, "gt", $expectedValue);
    }

    /**
     * Adds a filter condition with an "and less than" clause.
     *
     * @param string $columnName
     * @param int|float $expectedValue
     * @return $this
     */
    public function andWhereLessThan(string $columnName, $expectedValue): self
    {
        return $this->andWhere($columnName, "lt", $expectedValue);
    }

    /**
     * Set query limit ($top).
     *
     * @param int $limit Max amount of records to return.
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set query offset ($skip).
     *
     * @param int $offset Amount of records to skip.
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Generates a list of query parameters for the final request URL.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        $query = [];

        // $select - string - Comma separated list of column names to return. "nameDepartment,fullname,category".
        //                    Can also use aggregation functions and modifiers: "Count(category):Footer"
        if (!empty($this->select)) {
            $query['$select'] = implode(",", $this->select);
        }

        if (!empty($this->filter)) {
            $query['$filter'] = $this->filter;
        }

        // $top	- int32 - Number of rows to return in results
        if ($this->limit !== 0) {
            $query['$top'] = $this->limit;
        }

        // $skip - int32 - Number of rows from database to skip before returning results
        if ($this->offset !== 0) {
            $query['$skip'] = $this->offset;
        }

        return $query;
    }

    /**
     * Generates the query parameters and represents it as unencoded query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        $params = $this->getQueryParams();
        $str = "";

        foreach ($params as $key => $value) {
            if (!empty($str)) {
                $str .= "&";
            }

            $str .= $key;
            $str .= '=';
            $str .= $value;
        }

        return $str;
    }

    /**
     * Executes this query on the collection.
     * This is a helper function that simply calls $collection->executeQuery().
     *
     * @return array|Struct[]
     * @throws WebApiException
     */
    public function execute(): array
    {
        return $this->collection->executeQuery($this);
    }
}