<?php

namespace App\Http\Controllers\Api\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NewCommentRequest;
use App\Http\Resources\Api\CommentResource;
use App\Http\Resources\Api\CommentsCollection;
use App\Models\Article;
use App\Models\Comment;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param string $slug
     * @return \App\Http\Resources\Api\CommentsCollection<Comment>
     */
    public function list(string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        return new CommentsCollection($article->comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Api\NewCommentRequest $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(NewCommentRequest $request, string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $comment = Comment::create([
            'article_id' => $article->getKey(),
            'author_id' => $user->getKey(),
            'body' => $request->input('comment.body'),
        ]);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $slug
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(string $slug, $id)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        $comment = $article->comments()
            ->findOrFail((int) $id);

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => trans('models.comment.deleted'),
        ]);
    }
}
