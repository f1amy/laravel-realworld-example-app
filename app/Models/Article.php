<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Article
 *
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'author_id',
        'slug',
        'title',
        'description',
        'body',
    ];

    /**
     * Determine if user favored the article.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function favoredBy(User $user): bool
    {
        return $this->favoredUsers()
            ->whereKey($user->getKey())
            ->exists();
    }

    /**
     * Article author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Article tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the comments for the article.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get users favored the article.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoredUsers()
    {
        return $this->belongsToMany(User::class, 'article_favorite');
    }
}
