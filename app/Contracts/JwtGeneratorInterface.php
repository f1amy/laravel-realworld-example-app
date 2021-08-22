<?php

namespace App\Contracts;

interface JwtGeneratorInterface
{
    /**
     * Generate JWT signature.
     *
     * @param \App\Contracts\JwtTokenInterface $token
     * @return string
     */
    public static function signature(JwtTokenInterface $token): string;

    /**
     * Generate JWT string.
     *
     * @param \App\Contracts\JwtSubjectInterface $user
     * @return string
     */
    public static function token(JwtSubjectInterface $user): string;
}
