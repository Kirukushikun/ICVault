<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tip>
 */
class TipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'body' => $this->faker->sentence(),
            'source_question_id' => null,
        ];
    }
}
