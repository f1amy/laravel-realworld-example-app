<?php

namespace App\Contracts;

interface JwtValidator
{
    /**
     * Validate JwToken signature, header, expiration and subject.
     *
     * @param \App\Contracts\JwToken $token
     * @return bool
     */
    public static function validate(JwToken $token): bool;
}
