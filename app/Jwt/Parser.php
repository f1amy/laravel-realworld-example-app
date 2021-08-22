<?php

namespace App\Jwt;

use App\Contracts\JwtTokenInterface;
use App\Contracts\JwtParserInterface;
use App\Exceptions\JwtParseException;

class Parser implements JwtParserInterface
{
    /**
     * @param string $token
     * @return \App\Contracts\JwtTokenInterface
     * @throws \App\Exceptions\JwtParseException
     * @throws \JsonException
     */
    public static function parse(string $token): JwtTokenInterface
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new JwtParseException('JwtToken parts count does not match.');
        }

        $base64Decoded = array_map(function ($part) {
            /** @var false|string $decoded */
            $decoded = base64_decode($part, true);

            if ($decoded === false) {
                throw new JwtParseException('JwtToken parts base64 decode error.');
            }

            return $decoded;
        }, $parts);

        [$jsonHeader, $jsonPayload, $signature] = $base64Decoded;

        $jsonDecoded = array_map(fn (string $part) =>
            json_decode($part, true, 512, JSON_THROW_ON_ERROR),
            [$jsonHeader, $jsonPayload]
        );

        [$header, $payload] = $jsonDecoded;

        return new Token($header, $payload, $signature);
    }
}
