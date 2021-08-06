<?php

namespace App\Models;

use App\Casts\File;
use App\Contracts\JwtSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements JwtSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'bio',
        'image',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string|class-string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'image' => File::class,
    ];

    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Determine if user is following an author.
     *
     * @param \App\Models\User $author
     * @return bool
     */
    public function following(User $author): bool
    {
        return $this->authors()
            ->whereKey($author->getKey())
            ->exists();
    }

    /**
     * Determine if author followed by a user.
     *
     * @param \App\Models\User $follower
     * @return bool
     */
    public function followedBy(User $follower): bool
    {
        return $this->followers()
            ->whereKey($follower->getKey())
            ->exists();
    }

    /**
     * The authors that the user follows.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function authors()
    {
        return $this->belongsToMany(User::class, 'author_follower', 'author_id', 'follower_id');
    }

    /**
     * The followers of the author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'author_follower', 'follower_id', 'author_id');
    }

    /**
     * Get the comments of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    /**
     * Get user written articles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Get user favorite articles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites()
    {
        return $this->belongsToMany(Article::class, 'article_favorite');
    }
}
