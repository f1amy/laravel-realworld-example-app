<?php

namespace App\Contracts;

interface JwtSubject
{
    /**
     * Get JWT subject identifier (User Key).
     *
     * @return mixed
     */
    public function getJwtIdentifier();
}
