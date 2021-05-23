<?php

namespace App\Http\Resources;

use App\Jwt\Generator;

class UserTokenResource extends UserResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return parent::toArray($request) + [
            'token' => Generator::token($this->resource),
        ];
    }
}
