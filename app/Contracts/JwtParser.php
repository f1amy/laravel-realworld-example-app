<?php

namespace App\Contracts;

interface JwtParser
{
    /**
     * Parse JWT and return JwToken instance.
     * Use JwtValidator to verify the token.
     *
     * @see \App\Contracts\JwtValidator::validate()
     * @param string $token
     * @return \App\Contracts\JwToken
     */
    public static function parse(string $token): JwToken;
}
