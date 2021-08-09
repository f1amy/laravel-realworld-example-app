<?php

namespace App\Jwt;

use App\Contracts\JwToken;
use Illuminate\Support\Collection;

class Token implements JwToken
{
    /** User-supplied signature */
    private ?string $signature;
    private Collection $header;
    private Collection $payload;

    /**
     * JwToken constructor.
     *
     * @param array<string, mixed>|null $headers
     * @param array<string, mixed>|null $claims
     * @param string|null $signature User-supplied signature
     */
    public function __construct(
        array $headers = null,
        array $claims = null,
        string $signature = null)
    {
        $this->header = collect(
            array_merge($this->getDefaultHeaders(), $headers ?? [])
        );
        $this->payload = collect($claims);
        $this->signature = $signature;
    }

    public function getDefaultHeaders(): array
    {
        $headers = config('jwt.headers');

        return is_array($headers) ? $headers : [];
    }

    public function headers(): Collection
    {
        return clone $this->header;
    }

    public function claims(): Collection
    {
        return clone $this->payload;
    }

    public function getUserSignature(): ?string
    {
        return $this->signature;
    }

    public function putToPayload($key, $value): void
    {
        $this->payload->put($key, $value);
    }

    public function putToHeader($key, $value): void
    {
        $this->header->put($key, $value);
    }

    public function setUserSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getSubject()
    {
        return $this->payload->get('sub');
    }

    public function getExpiration(): int
    {
        return (int) $this->payload->get('exp');
    }
}
