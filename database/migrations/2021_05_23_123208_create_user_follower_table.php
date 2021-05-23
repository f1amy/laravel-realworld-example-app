<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFollowerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author_follower', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('follower_id')->constrained('users');

            $table->unique(['author_id', 'follower_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('author_follower');
    }
}
