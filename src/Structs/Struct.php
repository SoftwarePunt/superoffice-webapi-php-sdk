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

            if ($value instanceof Struct) {
                $value = $value->asArray();
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
            // Hack for handling keys like "odata.metadata";
            // when our local php property has a double underscore (__) treat it as a dot on the key name
            $sourceKeyName = str_replace("__", ".", $propName);

            // Get value from source array; if it doesn't contain it, assume NULL
            $value = null;

            if (isset($sourceArray[$sourceKeyName])) {
                $value = $sourceArray[$sourceKeyName];
            }

            // Get type information for the property
            $phpType = $this->propTypes[$propName];

            if ($phpType) {
                // Check if the php type is a class, we may need to do conversion magic
                $phpTypeStr = $phpType->getName();

                // Determine whether this property is nullable
                $allowNull = ($phpType instanceof \ReflectionNamedType && $phpType->allowsNull());

                // Handle serialized objects, e.g. converting from array to struct or string to datetime
                if (!is_object($value) && class_exists($phpTypeStr)) {
                    switch ($phpTypeStr) {
                        case "DateTime":
                        case "\DateTime":
                            // Try to parse as DateTime value
                            $value = new \DateTime($value);
                            break;
                        default:
                            if (is_array($value)) {
                                // If it's an array, it could be a serialized struct
                                // Try to initialize as an object, see if it inherits from Struct
                                $testObj = new $phpTypeStr();

                                if ($testObj && $testObj instanceof Struct) {
                                    $testObj->fillFromArray($value);
                                    $value = $testObj;
                                }
                            }
                            break;
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