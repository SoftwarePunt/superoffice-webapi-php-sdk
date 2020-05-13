<?php

namespace roydejong\SoWebApi\Structs;

/**
 * Helper class for representing data structures as convertible objects.
 */
abstract class Struct
{
    /**
     * Array containing names for all non-static, public properties belonging to this class.
     *
     * @var string[]
     */
    protected array $propNames;

    /**
     * Array containing type information for this class' properties, indexed by property name.
     *
     * @see Struct::$propNames
     * @var array
     */
    protected array $propTypes;

    /**
     * Initializes a new struct instance, with an optional array of values.
     *
     * @param array|null $input Optional input array to process.
     */
    public function __construct(?array $input = null)
    {
        $this->initializeProperties();

        if ($input && is_array($input)) {
            $this->fillFromArray($input);
        }
    }

    /**
     * Indexes property names and type data for this struct class.
     */
    protected function initializeProperties(): void
    {
        $this->propNames = [];

        try {
            $rfClass = new \ReflectionClass($this);
            $rfProperties = $rfClass->getProperties(\ReflectionProperty::IS_PUBLIC);

            foreach ($rfProperties as $rfProperty) {
                if ($rfProperty->isStatic()) {
                    // Ignore static properties, only process our own public properties
                    continue;
                }

                $propName = $rfProperty->getName();

                $this->propNames[] = $propName;
                $this->propTypes[$propName] = $rfProperty->getType();
            }
        } catch (\ReflectionException $rex) {
            // This should never happen, as "$this" is always a valid class
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

        foreach ($this->propNames as $propName) {
            // Get value from object, if it's unset, assume NULL
            $value = (isset($this->$propName) ? $this->$propName : null);

            // Perform value conversions as needed
            if ($value instanceof \DateTime) {
                $value = $value->format('c');
            }

            // Finally, add to array
            $array[$propName] = $value;
        }

        return $array;
    }

    /**
     * Fills this struct from an associative array.
     *
     * @param array $sourceArray
     * @throws
     */
    public function fillFromArray(array $sourceArray): void
    {
        foreach ($this->propNames as $propName) {
            // Get value from source array; if it doesn't contain it, assume NULL
            $value = null;

            if (isset($sourceArray[$propName])) {
                $value = $sourceArray[$propName];
            }

            // Get type information for the property
            $phpType = $this->propTypes[$propName];

            if ($phpType) {
                // Typed property, convert if needed
                $allowNull = ($phpType instanceof \ReflectionNamedType && $phpType->allowsNull());

                // Check if the php type is a class, we may need to do conversion magic
                $phpTypeStr = $phpType->getName();

                if (!is_object($value) && class_exists($phpTypeStr)) {
                    if ($phpTypeStr === "DateTime" || $phpTypeStr === "\DateTime") {
                        // Try to parse as DateTime value
                        $value = new \DateTime($value);
                    } else {
                        // TODO: Sub structs
                    }
                }

                // Finally, apply property value
                if ($value !== null || $allowNull) {
                    $this->$propName = $value;
                } else {
                    // Missing key or null value, but property type is not nullable; ensure it's unset instead
                    unset($this->$propName);
                }
            } else {
                // Untyped property, just set it
                $this->$propName = $value;
            }
        }
    }
}