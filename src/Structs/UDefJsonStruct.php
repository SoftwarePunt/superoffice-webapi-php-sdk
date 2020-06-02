<?php

namespace roydejong\SoWebApi\Structs;

/**
 * A JsonStruct with UserDefinedFields.
 */
abstract class UDefJsonStruct extends JsonStruct
{
    public array $UserDefinedFields;

    /**
     * Gets a string value from the UserDefinedFields for this struct.
     *
     * @param string $progId The "Prog ID" (programmatic id) configured for this UDef field in SuperOffice.
     * @return string|null The string value. NULL is returned if the key does not exist.
     */
    public function getUserString(string $progId): ?string
    {
        if (!isset($this->UserDefinedFields[$progId]) || $this->UserDefinedFields[$progId] === null) {
            return null;
        }

        return strval($this->UserDefinedFields[$progId]);
    }

    /**
     * Gets an integer value from the UserDefinedFields for this struct.
     *
     * @param string $progId The "Prog ID" (programmatic id) configured for this UDef field in SuperOffice.
     * @return int The integer value, or 0 if parsing fails or the key is invalid.
     */
    public function getUserInt(string $progId): int
    {
        $str = $this->getUserString($progId);

        if ($str === null) {
            return 0;
        }

        return intval($str);
    }

    /**
     * Gets a boolean value from the UserDefinedFields for this struct.
     *
     * @param string $progId The "Prog ID" (programmatic id) configured for this UDef field in SuperOffice.
     * @return bool The boolean value.
     */
    public function getUserBool(string $progId): bool
    {
        $str = $this->getUserString($progId);

        if ($str === null) {
            return false;
        }

        $value = strtolower($str);
        return $value === "true" || $value === "1";
    }

    /**
     * Gets a DateTime value from the UserDefinedFields for this struct.
     *
     * @param string $progId The "Prog ID" (programmatic id) configured for this UDef field in SuperOffice.
     * @return \DateTime|null The DateTime value, or NULL if parsing fails.
     */
    public function getUserDate(string $progId): ?\DateTime
    {
        $str = $this->getUserString($progId);

        if (empty($str)) {
            return null;
        }

        $parsed = null;

        try {
            if (strpos($str, "[D:") === 0) {
                // SuperOffice date format
                // example: [D:01/01/0001]
                $parsed = \DateTime::createFromFormat(
                    "\[\D:d/m/Y]",
                    $str
                );
            } else {
                // Default mode: any DateTime
                $parsed = new \DateTime($str);
            }
        } catch (\Exception $ex) { }

        return $parsed;
    }
}