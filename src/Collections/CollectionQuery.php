<?php

namespace roydejong\SoWebApi\Collections;

class CollectionQuery
{
    protected Collection $collection;

    protected array $select;
    protected int $limit;
    protected int $offset;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;

        $this->select = [];
        $this->limit = 0;
        $this->offset = 0;
    }

    /**
     * Sets the list of columns  limit ($top).
     *
     * @param string|string[] $columns Max amount of records to return.
     * @return $this
     */
    public function select(string ...$columns): CollectionQuery
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
     * Set query limit ($top).
     *
     * @param int $limit Max amount of records to return.
     * @return $this
     */
    public function limit(int $limit): CollectionQuery
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
    public function offset(int $offset): CollectionQuery
    {
        $this->offset = $offset;
        return $this;
    }

    public function getQueryParams(): array
    {
        $query = [];

        // $select - string - Comma separated list of column names to return. "nameDepartment,fullname,category".
        //                    Can also use aggregation functions and modifiers: "Count(category):Footer"
        if (!empty($this->select)) {
            $query['$select'] = implode(",", $this->select);
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

    public function execute(): void
    {
        $this->collection->executeQuery($this);
    }
}