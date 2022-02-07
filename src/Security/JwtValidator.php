<?php

namespace roydejong\SoWebApi\Security;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Validator;
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
            $token = (new \Lcobucci\JWT\Token\Parser(new JoseEncoder()))
                ->parse($jwt);
        } catch (\InvalidArgumentException $ex) {
            throw new \InvalidArgumentException('Invalid JWT: could not parse token', $ex->getCode(), $ex);
        }

        // Define validation constraints
        $signer = new Sha256();
        $publicKey = Key\InMemory::file("file://" . self::getLoginCertificatePath($this->config->environment));

        $constraints = [
            // Token should be valid now, with 30 second leeway
            new ValidAt(SystemClock::fromSystemTimezone(), new \DateInterval('PT30S')),

            // Token should be issued by the configured SO environment (e.g https://sod.superoffice.com)
            new IssuedBy("https://{$this->config->environment}.superoffice.com"),

            // Should be signed by the SO login certificate for the current environment
            new SignedWith($signer, $publicKey)
        ];

        // Perform validation
        return (new Validator())->validate($token, ...$constraints);
    }

    public static function getLoginCertificatePath(string $environment): string
    {
        $libRoot = realpath(__DIR__ . "/../../");
        return $libRoot . "/certificates/{$environment}_login.crt";
    }
}