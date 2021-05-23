<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class TagsCollection
 *
 * @package App\Http\Resources
 * @property \App\Models\Tag[]|\Illuminate\Support\Collection $collection
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
     * @return array<string, mixed>|\Illuminate\Support\Collection<\App\Models\Tag|string>
     */
    public function toArray($request)
    {
        return $this->collection->pluck('name');
    }
}
