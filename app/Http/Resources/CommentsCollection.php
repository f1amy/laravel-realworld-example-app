<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class CommentsCollection
 *
 * @package App\Http\Resources
 * @property \App\Models\Comment[]|\Illuminate\Support\Collection $collection
 */
class CommentsCollection extends ResourceCollection
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'comments';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CommentResource::class;
}
