<?php

namespace roydejong\SoWebApiTests\Security;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use PHPUnit\Framework\TestCase;
use roydejong\SoWebApi\Config;
use roydejong\SoWebApi\Security\JwtValidator;

class JwtValidatorTest extends TestCase
{
    public function testGetLoginCertificatePaths()
    {
        $resultPath = JwtValidator::getLoginCertificatePath("sod");

        $this->assertSame(
            realpath(__DIR__ . "/../../") . "/certificates/sod_login.crt",
            $resultPath
        );

        $this->assertFileExists($resultPath);
    }

    public function testValidateBadToken()
    {
        $config = new Config([
            "environment" => "sod"
        ]);

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid JWT: could not parse token');

        $jv = new JwtValidator($config);
        $jv->validateAndVerifyJwt("BAD_TOKEN");
    }

    public function testValidateValidToken()
    {
        $config = new Config([
            "environment" => "unit_test"
        ]);

        $pathPrivKey = realpath(__DIR__ . "/../../") . "/certificates/unit_test_login.key";

        $signer = new Sha256();
        $privateKey = new Key("file://{$pathPrivKey}");

        $token = (new Builder())
            ->issuedAt(time())
            ->issuedBy('https://unit_test.superoffice.com')
            ->expiresAt(time() + 60)
            ->getToken($signer, $privateKey);

        $jv = new JwtValidator($config);
        $result = $jv->validateAndVerifyJwt($token);

        $this->assertTrue($result, "Valid token should validate");
    }

    public function testValidateExpiredTokenWithValidIssuer()
    {
        $config = new Config([
            "environment" => "unit_test"
        ]);

        $pathPrivKey = realpath(__DIR__ . "/../../") . "/certificates/unit_test_login.key";

        $signer = new Sha256();
        $privateKey = new Key("file://{$pathPrivKey}");

        $token = (new Builder())
            ->issuedAt(time() - 3600)
            ->issuedBy('https://unit_test.superoffice.com')
            ->expiresAt(time() - 3000)
            ->getToken($signer, $privateKey);

        $jv = new JwtValidator($config);
        $result = $jv->validateAndVerifyJwt($token);

        $this->assertFalse($result, "Expired token should not validate");
    }

    public function testValidateTokenWithInvalidIssuer()
    {
        $config = new Config([
            "environment" => "unit_test"
        ]);

        $pathPrivKey = realpath(__DIR__ . "/../../") . "/certificates/unit_test_login.key";

        $signer = new Sha256();
        $privateKey = new Key("file://{$pathPrivKey}");

        $token = (new Builder())
            ->issuedAt(time())
            ->issuedBy('https://bad_issuer.superoffice.com')
            ->expiresAt(time() + 3600)
            ->getToken($signer, $privateKey);

        $jv = new JwtValidator($config);
        $result = $jv->validateAndVerifyJwt($token);

        $this->assertFalse($result, "Token with invalid issuer should not validate");
    }

    public function testValidateValidTokenWithWrongCertificate()
    {
        $config = new Config([
            "environment" => "sod"
        ]);

        $pathPrivKey = realpath(__DIR__ . "/../../") . "/certificates/unit_test_login.key";

        $signer = new Sha256();
        $privateKey = new Key("file://{$pathPrivKey}");

        $token = (new Builder())
            ->issuedAt(time())
            ->issuedBy('https://sod.superoffice.com')
            ->expiresAt(time() + 60)
            ->getToken($signer, $privateKey);

        $jv = new JwtValidator($config);
        $result = $jv->validateAndVerifyJwt($token);

        $this->assertFalse($result, "Valid token should not validate with the wrong certificate / environment");
    }
}