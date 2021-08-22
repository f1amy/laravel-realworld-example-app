<?php

return [

    /**
     * Expiration of JWT Token in seconds.
     */
    'expiration' => (int) env('JWT_EXPIRATION', 3600), // one hour

    /**
     * Default JWT headers.
     */
    'headers' => [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ],

];
