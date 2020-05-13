<?php

namespace roydejong\SoWebApi;

/**
 * Configuration data for the SuperOffice API environment.
 */
class Config
{
    /**
     * Defines the base URL for the SuperOffice installation.
     *
     * @example https://sod2.superoffice.com/CustXXXX
     */
    public string $baseUrl;

    /**
     * Client ID (Application ID).
     */
    public string $clientId;

    /**
     * Client secret (Application Token).
     */
    public string $clientSecret;

    /**
     * Private key for system user token signing (<RSAKeyValue> block).
     */
    public string $privateKey;

    /**
     * @param array|null $values Optional array of configuration values to set.
     */
    public function __construct(?array $values = null)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                if (property_exists($this, $key) && is_string($value)) {
                    $this->$key = $value;
                } else {
                    throw new \InvalidArgumentException(
                        "Cannot set configuration key {$key}: invalid key, or value is not a string");
                }
            }
        }
    }
}