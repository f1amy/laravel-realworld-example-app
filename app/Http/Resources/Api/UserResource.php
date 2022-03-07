<?php

namespace App\Http\Resources\Api;

use App\Jwt;

class UserResource extends BaseUserResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'email' => $this->resource->email,
            'token' => Jwt\Generator::token($this->resource),
        ]);
    }
}
