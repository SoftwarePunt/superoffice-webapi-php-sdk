<?php

namespace roydejong\SoWebApi\Structs\UDef;

use roydejong\SoWebApi\Structs\JsonStruct;

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

    /**
     * Gets a user-defined list value from the UserDefinedFields for this struct.
     *
     * @param string $progId The "Prog ID" (programmatic id) configured for this UDef field in SuperOffice.
     * @return UDefListValue|null The user-defined list value, or NULL if parsing fails.
     */
    public function getUserListValue(string $progId): ?UDefListValue
    {
        // A linked list value has three keys in the $UserDefinedFields array
        $strId = $this->getUserString($progId);
        $strDisplayText = $this->getUserString("{$progId}:DisplayText") ?? "";
        $strDisplayTooltip = $this->getUserString("{$progId}:DisplayTooltip") ?? "";

        if (empty($strId) || strpos($strId, "[I:") !== 0) {
            // The ID reference should look like: [I:11]
            return null;
        }

        // Parse ID reference
        $strId = substr($strId, strlen("[I:"));
        $strId = substr($strId, 0, strlen($strId) - strlen("]"));
        $idValue = intval($strId);

        if ($idValue <= 0) {
            // Could not extract a valid ID
            return null;
        }

        $returnObj = new UDefListValue();
        $returnObj->Id = $strId;
        $returnObj->DisplayText = $strDisplayText;
        $returnObj->DisplayTooltip = $strDisplayTooltip;
        return $returnObj;
    }
}