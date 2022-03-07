<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class TagsCollection
 *
 * @package App\Http\Resources
 * @property \Illuminate\Support\Collection<\App\Models\Tag> $collection
 */
class TagsCollection extends ResourceCollection
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'tags';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TagResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function toArray($request)
    {
        return $this->collection->pluck('name');
    }
}
