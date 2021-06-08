<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ArticleResource
 *
 * @package App\Http\Resources
 * @property \App\Models\Article $resource
 */
class ArticleResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = 'article';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /**
         * @var \App\Models\User|null $user
         */
        $user = $request->user('api');

        return [
            'slug' => $this->resource->slug,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'body' => $this->resource->body,
            'tagList' => new TagsCollection($this->resource->tags),
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
            'favorited' => $this->when($user !== null, function () use ($user) {
                /**
                 * @var \App\Models\User $user
                 */
                return $this->resource->favoredBy($user);
            }),
            'favoritesCount' => $this->resource->favoredUsers()->count(),
            'author' => new ProfileResource($this->resource->author),
        ];
    }
}
