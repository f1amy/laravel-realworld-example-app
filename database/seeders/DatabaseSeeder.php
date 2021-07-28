<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Article::factory()
            ->count(10)
            ->has(Tag::factory()->count(3))
            ->has(Comment::factory()->count(5))
            ->create();
    }
}
