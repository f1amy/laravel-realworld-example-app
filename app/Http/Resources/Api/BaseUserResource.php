<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BaseUserResource
 *
 * @package App\Http\Resources
 * @property \App\Models\User $resource
 */
abstract class BaseUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'username' => $this->resource->username,
            'bio' => $this->resource->bio,
            'image' => $this->resource->image,
        ];
    }
}
