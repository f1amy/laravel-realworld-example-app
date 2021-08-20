<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CommentResource
 *
 * @package App\Http\Resources
 * @property \App\Models\Comment $resource
 */
class CommentResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = 'comment';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->getKey(),
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
            'body' => $this->resource->body,
            'author' => new ProfileResource($this->resource->author),
        ];
    }
}
