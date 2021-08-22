<?php

namespace App\Contracts;

interface JwtGenerator
{
    /**
     * Generate JWT signature.
     *
     * @param \App\Contracts\JwtToken $token
     * @return string
     */
    public static function signature(JwtToken $token): string;

    /**
     * Generate JWT string.
     *
     * @param \App\Contracts\JwtSubject $user
     * @return string
     */
    public static function token(JwtSubject $user): string;
}
