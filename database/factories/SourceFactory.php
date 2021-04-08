<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Source::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'              => $this->faker->sentence(3),
            'description'       => $this->faker->sentence(8),
            'url'               => $this->faker->url,
            'author'            => $this->faker->name,
            'fetcher_kind'      => 'rss',
            'fetcher_source'    => $this->faker->url,
            'active'            => 1,
            'why_deactivated'   => null,
            'category_id'       => 1 //default
        ];
    }
}
