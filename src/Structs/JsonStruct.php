<?php

namespace roydejong\SoWebApi\Structs;

/**
 * Helper class for quickly converting between typed PHP objects and JSON data.
 */
abstract class JsonStruct extends Struct
{
    /**
     * Initializes a new instance of this JSON struct, optionally with raw JSON input.
     *
     * @param string|null $json Optional JSON input string to parse.
     */
    public function __construct(?string $json = null)
    {
        parent::__construct();

        if ($json) {
            $this->fillFromJson($json);
        }
    }

    /**
     * Fills this struct from raw JSON input.
     *
     * @param string $jsonInput
     */
    protected function fillFromJson(string $jsonInput): void
    {
        $parsedArray = @json_decode($jsonInput, true);

        if (!$parsedArray || !is_array($parsedArray)) {
            throw new \InvalidArgumentException("Could not parse string as JSON: {$jsonInput}");
        }

        $this->fillFromArray($parsedArray);
    }

    /**
     * Convert this struct into a JSON string.
     */
    public function asJson(): string
    {
        return json_encode($this->asArray());
    }
}