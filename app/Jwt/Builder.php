<?php

namespace App\Jwt;

use App\Contracts\JwtBuilderInterface;
use App\Contracts\JwtTokenInterface;
use App\Contracts\JwtSubjectInterface;

class Builder implements JwtBuilderInterface
{
    private JwtTokenInterface $jwt;

    public function __construct()
    {
        $this->jwt = new Token();
    }

    public static function build(): JwtBuilderInterface
    {
        return new self();
    }

    public function issuedAt(int $timestamp): JwtBuilderInterface
    {
        $this->jwt->putToPayload('iat', $timestamp);

        return $this;
    }

    public function expiresAt(int $timestamp): JwtBuilderInterface
    {
        $this->jwt->putToPayload('exp', $timestamp);

        return $this;
    }

    public function subject(mixed $identifier): JwtBuilderInterface
    {
        if ($identifier instanceof JwtSubjectInterface) {
            $identifier = $identifier->getJwtIdentifier();
        }

        $this->jwt->putToPayload('sub', $identifier);

        return $this;
    }

    public function withClaim(string $key, mixed $value = null): JwtBuilderInterface
    {
        $this->jwt->putToPayload($key, $value);

        return $this;
    }

    public function withHeader(string $key, mixed $value = null): JwtBuilderInterface
    {
        $this->jwt->putToHeader($key, $value);

        return $this;
    }

    public function getToken(): JwtTokenInterface
    {
        return $this->jwt;
    }
}
