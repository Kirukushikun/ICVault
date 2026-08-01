<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tip>
 */
class TipFactory extends Factory
{
    /** @var class-string<Tip> */
    protected $model = Tip::class;

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
