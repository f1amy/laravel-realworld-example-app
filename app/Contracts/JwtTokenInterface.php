<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface JwtTokenInterface
{
    /**
     * Get default headers.
     *
     * @return array<string>
     */
    public function getDefaultHeaders(): array;

    /**
     * Get a copy of headers set on token.
     *
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public function headers(): Collection;

    /**
     * Get a copy of claims set on token.
     *
     * @return \Illuminate\Support\Collection<string, mixed>
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
     * @param string $key
     * @param mixed $value
     */
    public function putToPayload(string $key, mixed $value): void;

    /**
     * Put value to header under a key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function putToHeader(string $key, mixed $value): void;

    /**
     * Set user-supplied signature.
     *
     * @param string $signature
     */
    public function setUserSignature(string $signature): void;

    /**
     * Get subject key.
     *
     * @return mixed
     */
    public function getSubject(): mixed;

    /**
     * Get expiration timestamp.
     *
     * @return int
     */
    public function getExpiration(): int;
}
