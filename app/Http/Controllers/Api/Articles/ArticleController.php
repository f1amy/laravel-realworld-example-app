<?php

namespace App\Http\Controllers\Api\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ArticleListRequest;
use App\Http\Requests\Api\FeedRequest;
use App\Http\Requests\Api\NewArticleRequest;
use App\Http\Requests\Api\UpdateArticleRequest;
use App\Http\Resources\Api\ArticleResource;
use App\Http\Resources\Api\ArticlesCollection;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ArticleController extends Controller
{
    /** @var int Default limit for feed listing. */
    protected const FILTER_LIMIT = 20;

    /** @var int Default offset for feed listing. */
    protected const FILTER_OFFSET = 0;

    /**
     * Display global listing of the articles.
     *
     * @param \App\Http\Requests\Api\ArticleListRequest $request
     * @return \App\Http\Resources\Api\ArticlesCollection<Article>
     */
    public function list(ArticleListRequest $request)
    {
        $filter = collect($request->validated());

        $limit = (int) $filter->get('limit', static::FILTER_LIMIT);
        $offset = (int) $filter->get('offset', static::FILTER_OFFSET);

        $list = Article::orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

        if ($tag = $filter->get('tag')) {
            $list->whereHas('tags', fn (Builder $query) =>
                $query->where('name', $tag)
            );
        }

        if ($authorName = $filter->get('author')) {
            $list->whereHas('author', fn (Builder $query) =>
                $query->where('username', $authorName)
            );
        }

        if ($userName = $filter->get('favorited')) {
            $list->whereHas('favoredUsers', fn (Builder $query) =>
                $query->where('username', $userName)
            );
        }

        return new ArticlesCollection($list->get());
    }

    /**
     * Display article feed for the user.
     *
     * @param \App\Http\Requests\Api\FeedRequest $request
     * @return \App\Http\Resources\Api\ArticlesCollection<Article>
     */
    public function feed(FeedRequest $request)
    {
        $filter = collect($request->validated());

        $limit = (int) $filter->get('limit', static::FILTER_LIMIT);
        $offset = (int) $filter->get('offset', static::FILTER_OFFSET);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $feed = Article::whereHas('author', fn (Builder $query) =>
                $query->whereIn('id', $user->authors->pluck('id'))
            )
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return new ArticlesCollection($feed);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Api\NewArticleRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function create(NewArticleRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $attributes = Arr::get($request->validated(), 'article');
        $attributes['author_id'] = $user->getKey();

        $tags = Arr::pull($attributes, 'tagList');
        $article = Article::create($attributes);

        if (is_array($tags)) {
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                ]);

                $article->tags()->syncWithoutDetaching($tag);
            }

            $article->refresh();
        }

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return \App\Http\Resources\Api\ArticleResource
     */
    public function show(string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        return new ArticleResource($article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\UpdateArticleRequest $request
     * @param string $slug
     * @return \App\Http\Resources\Api\ArticleResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateArticleRequest $request, string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        $this->authorize('update', $article);

        $attributes = Arr::get($request->validated(), 'article');

        $article->update($attributes);

        return new ArticleResource($article);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        $this->authorize('delete', $article);

        $article->delete(); // cascade

        return response()->json(['message' => 'Article deleted.']);
    }
}
