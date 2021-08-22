<?php

namespace App\Jwt;

use App\Contracts\JwtTokenInterface;
use App\Contracts\JwtValidatorInterface;
use App\Models\User;
use Illuminate\Support\Carbon;

class Validator implements JwtValidatorInterface
{
    public static function validate(JwtTokenInterface $token): bool
    {
        $signature = $token->getUserSignature();
        if ($signature === null) {
            return false;
        }
        if (!hash_equals(Generator::signature($token), $signature)) {
            return false;
        }

        $headers = $token->headers();
        if ($headers->get('alg') !== 'HS256'
            || $headers->get('typ') !== 'JWT') {
            return false;
        }

        $expiresAt = $token->getExpiration();
        if ($expiresAt === 0) {
            return false;
        }
        $expirationDate = Carbon::createFromTimestamp($expiresAt);
        $currentDate = Carbon::now();
        if ($expirationDate->lessThan($currentDate)) {
            return false;
        }

        $subject = $token->getSubject();
        if (User::whereKey($subject)->doesntExist()) {
            return false;
        }

        return true;
    }
}
