<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface JwtTokenInterface
{
    /**
     * Get default headers.
     *
     * @return string[]
     */
    public function getDefaultHeaders(): array;

    /**
     * Get a copy of headers set on token.
     *
     * @return \Illuminate\Support\Collection
     */
    public function headers(): Collection;

    /**
     * Get a copy of claims set on token.
     *
     * @return \Illuminate\Support\Collection
     */
    public function claims(): Collection;

    /**
     * Get saved user-supplied signature.
     *
     * @return string|null
     */
    public function getUserSignature(): ?string;

    /**
     * Put value to payload under a key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function putToPayload($key, $value): void;

    /**
     * Put value to header under a key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function putToHeader($key, $value): void;

    /**
     * Set user-supplied signature.
     *
     * @param string $signature
     */
    public function setUserSignature(string $signature): void;

    /**
     * Get subject key.
     *
     * @return mixed|null
     */
    public function getSubject();

    /**
     * Get expiration timestamp.
     *
     * @return int
     */
    public function getExpiration(): int;
}
