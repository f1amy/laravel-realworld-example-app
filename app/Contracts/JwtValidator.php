<?php

namespace App\Contracts;

interface JwtValidator
{
    /**
     * Validate JwtToken signature, header, expiration and subject.
     *
     * @param \App\Contracts\JwtToken $token
     * @return bool
     */
    public static function validate(JwtToken $token): bool;
}
