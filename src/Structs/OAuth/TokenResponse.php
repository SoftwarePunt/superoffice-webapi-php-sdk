<?php

namespace roydejong\SoWebApi\Structs\OAuth;

use roydejong\SoWebApi\Structs\JsonStruct;

class TokenResponse extends JsonStruct
{
    const TOKEN_TYPE_BEARER = "Bearer";

    /**
     * Token type (usually "Bearer").
     */
    public string $token_type;

    /**
     * The access token issued by the authorization server.
     */
    public string $access_token;

    /**
     * The lifetime in seconds of the access token.
     */
    public int $expires_in;

    /**
     * The refresh token which can be used to obtain new access tokens.
     * The refresh token is a long lived token that can be re-used.
     *
     * It is coupled to an end-users consent and is valid as long as the application authorization (consent) exists.
     *
     * May be set to NULL if this was a refresh.
     */
    public ?string $refresh_token = null;

    /**
     * JSON Web Token (JWT), which consists of a Header, Payload and Signature.
     * This can be used to verify that the tokens came from the real SuperId server.
     */
    public string $id_token;
}