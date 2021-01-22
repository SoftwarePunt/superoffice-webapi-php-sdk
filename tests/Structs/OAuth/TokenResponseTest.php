<?php

namespace roydejong\SoWebApiTests\Structs\OAuth;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Config;
use roydejong\SoWebApi\Structs\OAuth\TokenResponse;

class TokenResponseTest extends TestCase
{
    // -----------------------------------------------------------------------------------------------------------------
    // Helpers

    private function getImmutableDateTimeWithOffset(int $offset)
    {
        return (new \DateTimeImmutable())
            ->setTimestamp(time() + $offset);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tests actual

    public function testValidateAndVerifyJwtWithBogusToken()
    {
        // Sample JWT: bogus, immediate parser failure
        $tokenResponse = new TokenResponse();
        $tokenResponse->id_token = "INVALID";

        $result = $tokenResponse->validateAndVerifyJwt(new Config(['environment' => 'sod']));
        $this->assertFalse($result, "Invalid token should return FALSE on validation, without throwing.");
    }

    public function testValidateAndVerifyJwtWithExpiredToken()
    {
        // Sample JWT: expired a long time ago but valid issuer
        $tokenResponse = new TokenResponse();
        $tokenResponse->id_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxMzE2MjM5MDIyLCJpc3MiOiJodHRwczovL3NvZC5zdXBlcm9mZmljZS5jb20iLCJleHAiOjE0MTYyMzkwMjJ9.NZWWXYj4kB1a6dsSlN_RGVkgJAsqLT4Mn5YaYlKjchY";

        $result = $tokenResponse->validateAndVerifyJwt(new Config(['environment' => 'sod']));
        $this->assertFalse($result, "Expired token should return FALSE on validation.");
    }

    public function testValidateAndVerifyValidJwt()
    {
        $pathPrivKey = realpath(__DIR__ . "/../../../") . "/certificates/unit_test_login.key";

        $signer = new Sha256();
        $privateKey = Key\LocalFileReference::file("file://{$pathPrivKey}");

        $token = (new \Lcobucci\JWT\Token\Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedAt($this->getImmutableDateTimeWithOffset(0))
            ->issuedBy('https://unit_test.superoffice.com')
            ->expiresAt($this->getImmutableDateTimeWithOffset(+60))
            ->getToken($signer, $privateKey);

        $tokenResponse = new TokenResponse();
        $tokenResponse->id_token = $token->toString();

        $result = $tokenResponse->validateAndVerifyJwt(new Config(['environment' => 'unit_test']));
        $this->assertTrue($result, "Valid token should return TRUE on validation.");
    }
}