<?php

namespace App\Contracts;

interface JwtSubjectInterface
{
    /**
     * Get JWT subject identifier (User Key).
     *
     * @return mixed
     */
    public function getJwtIdentifier(): mixed;
}
