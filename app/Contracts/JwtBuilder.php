<?php

namespace App\Contracts;

interface JwtBuilder
{
    /**
     * Start building JwToken.
     *
     * @return \App\Contracts\JwtBuilder
     */
    public static function build(): JwtBuilder;

    /**
     * Add issued at (iat) claim to the payload.
     *
     * @param int $timestamp
     * @return \App\Contracts\JwtBuilder
     */
    public function issuedAt(int $timestamp): JwtBuilder;

    /**
     * Add expires at (exp) claim to the payload.
     *
     * @param int $timestamp
     * @return \App\Contracts\JwtBuilder
     */
    public function expiresAt(int $timestamp): JwtBuilder;

    /**
     * Add subject (sub) claim to the payload.
     *
     * @param JwtSubject|mixed $identifier
     * @return \App\Contracts\JwtBuilder
     */
    public function subject($identifier): JwtBuilder;

    /**
     * Add custom claim to the payload.
     *
     * @param string $key
     * @param null|mixed $value
     * @return \App\Contracts\JwtBuilder
     */
    public function withClaim(string $key, $value = null): JwtBuilder;

    /**
     * Add custom header.
     *
     * @param string $key
     * @param null|mixed $value
     * @return \App\Contracts\JwtBuilder
     */
    public function withHeader(string $key, $value = null): JwtBuilder;

    /**
     * Get JwToken built.
     *
     * @return \App\Contracts\JwToken
     */
    public function getToken(): JwToken;
}
