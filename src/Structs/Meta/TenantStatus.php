<?php

namespace SoftwarePunt\SoWebApi\Structs\Meta;

use SoftwarePunt\SoWebApi\Structs\JsonStruct;

class TenantStatus extends JsonStruct
{
    const STATE_RUNNING = "Running";

    /**
     * Context identifier, for example, Cust00000.
     */
    public string $ContextIdentifier;

    /**
     * The root path of customer installation.
     *
     * This path will change routinely to balance the load.
     * Changes may also occur under special circumstances such as incidents.
     */
    public ?string $Endpoint;

    /**
     * Tenant status. Should be "Running" if the environment is available.
     */
    public string $State;

    /**
     * This indicates whether the tenant is up and running.
     */
    public bool $IsRunning;

    /**
     * When to check next time if an updated state is needed.
     */
    public \DateTime $ValidUntil;
}