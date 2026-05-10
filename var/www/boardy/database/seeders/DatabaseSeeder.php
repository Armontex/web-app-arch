<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(5)->create();

        Post::factory(10)
            ->recycle($users)
            ->create()
            ->each(function (Post $post) use ($users): void {
                Comment::factory(2)
                    ->recycle($users)
                    ->create([
                        'post_id' => $post->id,
                    ]);
            });
    }
}
