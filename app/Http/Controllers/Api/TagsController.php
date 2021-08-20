<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TagsCollection;
use App\Models\Tag;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Api\TagsCollection<Tag>
     */
    public function list()
    {
        return new TagsCollection(Tag::all());
    }
}
