<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(6),
            'url' => $this->faker->url(),
            'excerpt' => $this->faker->sentence(14),
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
            'posted_at' => Carbon::now()->subMinutes(rand(0, 5000)),
            'read' => 0,
            'uid' => Str::uuid(),
            'author' => $this->faker->name,
            // Associations will be set explicitly in tests usually.
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
