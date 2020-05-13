<?php

namespace roydejong\SoWebApi\Structs;

use roydejong\SoWebApi\WebApiException;

/**
 * Helper class for quickly converting between typed PHP objects and JSON data.
 */
abstract class JsonStruct
{
    /**
     * Initializes a new instance of this JSON struct, optionally with raw JSON input.
     *
     * @param string|null $json Optional JSON input to parse.
     */
    public function __construct(?string $json = null)
    {
        if ($json) {
            $this->fillFromJson($json);
        }
    }

    /**
     * Internal function for listing all public, non-static property names for to this class.
     *
     * @return array
     */
    protected function getPropertyNames(): array
    {
        $propNames = [];

        try {
            $rfClass = new \ReflectionClass($this);
            $rfProperties = $rfClass->getProperties(\ReflectionProperty::IS_PUBLIC);

            foreach ($rfProperties as $rfProperty) {
                if ($rfProperty->isStatic()) {
                    // Ignore static properties, only process our own public properties
                    continue;
                }

                $propNames[] = $rfProperty->getName();
            }
        } catch (\ReflectionException $rex) {
            // This should never happen, as $this is always a valid class
        }

        return $propNames;
    }

    /**
     * Fills this struct from raw JSON input.
     *
     * @param string $jsonInput
     */
    protected function fillFromJson(string $jsonInput): void
    {
        $parsed = @json_decode($jsonInput, true);

        if (!$parsed || !is_array($parsed)) {
            throw new \InvalidArgumentException("Could not parse string as JSON");
        }

        foreach ($this->getPropertyNames() as $propName) {
            if (isset($parsed[$propName])) {
                $value = $parsed[$propName];
                $this->$propName = $value;
            }
        }
    }

    /**
     * Convert this struct into an associative array containing all its public properties.
     *
     * @return array
     */
    public function asArray(): array
    {
        $array = [];

        foreach ($this->getPropertyNames() as $propName) {
            $array[$propName] = (isset($this->$propName) ? $this->$propName : null);
        }

        return $array;
    }

    /**
     * Convert this struct into a JSON string.
     */
    public function asJson(): string
    {
        return json_encode($this->asArray());
    }
}