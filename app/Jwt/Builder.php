<?php

namespace App\Jwt;

use App\Contracts\JwtBuilder;
use App\Contracts\JwToken;
use App\Contracts\JwtSubject;

class Builder implements JwtBuilder
{
    /** @var JwToken */
    private $jwt;

    public function __construct()
    {
        $this->jwt = new Token();
    }

    public static function build(): JwtBuilder
    {
        return new self();
    }

    public function issuedAt(int $timestamp): JwtBuilder
    {
        $this->jwt->putToPayload('iat', $timestamp);

        return $this;
    }

    public function expiresAt(int $timestamp): JwtBuilder
    {
        $this->jwt->putToPayload('exp', $timestamp);

        return $this;
    }

    public function subject($identifier): JwtBuilder
    {
        if ($identifier instanceof JwtSubject) {
            $identifier = $identifier->getJwtIdentifier();
        }

        $this->jwt->putToPayload('sub', $identifier);

        return $this;
    }

    public function withClaim(string $key, $value = null): JwtBuilder
    {
        $this->jwt->putToPayload($key, $value);

        return $this;
    }

    public function withHeader(string $key, $value = null): JwtBuilder
    {
        $this->jwt->putToHeader($key, $value);

        return $this;
    }

    public function getToken(): JwToken
    {
        return $this->jwt;
    }
}
