<?php

namespace App\Auth;

use App\Contracts\JwtTokenInterface;
use App\Exceptions\JwtParseException;
use App\Jwt;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonException;

/**
 * Class JwtGuard
 *
 * @package App\Auth
 * @property \Illuminate\Contracts\Auth\Authenticatable|null $user
 */
class JwtGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     */
    protected Request $request;

    /**
     * The name of the query string item from the request containing the API token.
     */
    protected string $inputKey;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @param  string $inputKey
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        string $inputKey = 'token')
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (! empty($token) && is_string($token)) {
            try {
                $jwt = Jwt\Parser::parse($token);
            } catch (JwtParseException | JsonException) {
                $jwt = null;
            }

            if ($this->validate([$this->inputKey => $jwt])) {
                /** @var \App\Contracts\JwtTokenInterface $jwt */
                $user = $this->provider->retrieveById(
                    $jwt->getSubject()
                );
            }
        }

        return $this->user = $user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array<string, mixed>  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $token = $credentials[$this->inputKey];

        if (! $token instanceof JwtTokenInterface) {
            return false;
        }

        return Jwt\Validator::validate($token);
    }

    /**
     * Get the token for the current request.
     *
     * @return mixed|null
     */
    public function getTokenForRequest()
    {
        $token = $this->jwtToken();

        if (empty($token)) {
            $token = $this->request->query($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        return $token;
    }

    /**
     * Get the JWT token from the request headers.
     *
     * @return string|null
     */
    public function jwtToken(): ?string
    {
        /** @var string $header */
        $header = $this->request->header('Authorization', '');

        if (Str::startsWith($header, 'Token ')) {
            return Str::substr($header, 6);
        }

        return null;
    }
}
