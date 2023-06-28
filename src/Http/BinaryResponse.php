<?php

namespace SoftwarePunt\SoWebApi\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Helper class for reading binary responses (for file downloads).
 */
class BinaryResponse
{
    protected ResponseInterface $httpResponse;

    public function __construct(ResponseInterface $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    public function getContentType(): string
    {
        return $this->httpResponse->getHeaderLine('Content-Type');
    }

    public function getContentAsString(): string
    {
        return (string)$this->httpResponse->getBody();
    }

    public function getContentAsStream(): StreamInterface
    {
        return $this->httpResponse->getBody();
    }
}