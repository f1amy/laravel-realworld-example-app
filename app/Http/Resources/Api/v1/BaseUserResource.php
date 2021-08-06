<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
        if (($image = $this->resource->image) !== null) {
            $image = Storage::url("images/{$image->getBasename()}");
        }

        return [
            'username' => $this->resource->username,
            'bio' => $this->resource->bio,
            'image' => $image,
        ];
    }
}
