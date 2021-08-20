<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TagResource
 *
 * @package App\Http\Resources
 * @property \App\Models\Tag $resource
 */
class TagResource extends JsonResource
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
            'name' => $this->resource->name,
        ];
    }
}
