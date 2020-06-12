<?php

namespace roydejong\SoWebApi\Security;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use roydejong\SoWebApi\Config;

class JwtValidator
{
    protected Config $config;

    /**
     * Initialize the JWT validator with configuration data.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Validates and verifies a given JWT token.
     *
     * @param string $jwt The raw, encoded JWT token.
     * @return bool Returns TRUE if the token could be verified and validated.
     *
     * @throws \InvalidArgumentException Throws if the provided token is invalid.
     */
    public function validateAndVerifyJwt(string $jwt): bool
    {
        // Try to parse the token
        $token = null;

        try {
            $token = (new Parser())->parse($jwt);
        } catch (\InvalidArgumentException $ex) {
            throw new \InvalidArgumentException('Invalid JWT: could not parse token', $ex->getCode(), $ex);
        }

        // Token should be valid now, with 30 second leeway
        $constraints = new ValidationData(time(), 30);
        // Token should be issued by the configured SO environment (e.g https://sod.superoffice.com)
        $constraints->setIssuer("https://{$this->config->environment}.superoffice.com");

        // Perform validation on constraints
        if (!$token->validate($constraints)) {
            return false;
        }

        // Perform verification against the SO login certificate for the current environment
        $signer = new Sha256();
        $publicKey = new Key("file://" . self::getLoginCertificatePath($this->config->environment));

        if (!$token->verify($signer, $publicKey)) {
            return false;
        }

        return true;
    }

    public static function getLoginCertificatePath(string $environment): string
    {
        $libRoot = realpath(__DIR__ . "/../../");
        return $libRoot . "/certificates/{$environment}_login.crt";
    }
}