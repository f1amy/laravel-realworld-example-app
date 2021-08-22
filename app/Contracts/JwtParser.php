<?php

namespace App\Contracts;

interface JwtParser
{
    /**
     * Parse JWT and return JwtToken instance.
     * Use JwtValidator to verify the token.
     *
     * @see \App\Contracts\JwtValidator::validate()
     * @param string $token
     * @return \App\Contracts\JwtToken
     */
    public static function parse(string $token): JwtToken;
}
