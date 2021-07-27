<?php

namespace App\Http\Controllers\Api\v1\Articles;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    /**
     * Add article to user's favorites.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \App\Http\Resources\ArticleResource
     */
    public function add(Request $request, string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        $user->favorites()->syncWithoutDetaching($article);

        return new ArticleResource($article);
    }

    /**
     * Remove article from user's favorites.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \App\Http\Resources\ArticleResource
     */
    public function remove(Request $request, string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        $user->favorites()->detach($article);

        return new ArticleResource($article);
    }
}
