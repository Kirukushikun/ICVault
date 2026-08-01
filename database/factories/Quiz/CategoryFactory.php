<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /** @var class-string<Category> */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'color' => $this->faker->hexColor(),
        ];
    }
}
