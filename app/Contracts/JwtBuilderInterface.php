<?php

namespace App\Contracts;

interface JwtBuilderInterface
{
    /**
     * Start building JwtToken.
     *
     * @return \App\Contracts\JwtBuilderInterface
     */
    public static function build(): JwtBuilderInterface;

    /**
     * Add issued at (iat) claim to the payload.
     *
     * @param int $timestamp
     * @return \App\Contracts\JwtBuilderInterface
     */
    public function issuedAt(int $timestamp): JwtBuilderInterface;

    /**
     * Add expires at (exp) claim to the payload.
     *
     * @param int $timestamp
     * @return \App\Contracts\JwtBuilderInterface
     */
    public function expiresAt(int $timestamp): JwtBuilderInterface;

    /**
     * Add subject (sub) claim to the payload.
     *
     * @param JwtSubjectInterface|mixed $identifier
     * @return \App\Contracts\JwtBuilderInterface
     */
    public function subject(mixed $identifier): JwtBuilderInterface;

    /**
     * Add custom claim to the payload.
     *
     * @param string $key
     * @param mixed|null $value
     * @return \App\Contracts\JwtBuilderInterface
     */
    public function withClaim(string $key, mixed $value = null): JwtBuilderInterface;

    /**
     * Add custom header.
     *
     * @param string $key
     * @param mixed|null $value
     * @return \App\Contracts\JwtBuilderInterface
     */
    public function withHeader(string $key, mixed $value = null): JwtBuilderInterface;

    /**
     * Get JwtToken built.
     *
     * @return \App\Contracts\JwtTokenInterface
     */
    public function getToken(): JwtTokenInterface;
}
