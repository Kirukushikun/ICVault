<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use App\Enums\MasteryState;
use App\Enums\QuestionType;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
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
            'import_batch_id' => null,
            'difficulty' => $this->faker->randomElement(Difficulty::cases()),
            'type' => $this->faker->randomElement(QuestionType::cases()),
            'prompt' => $this->faker->sentence(),
            'options_json' => null,
            'answer' => $this->faker->word(),
            'mastery_state' => MasteryState::New,
            'mastery_streak' => 0,
            'last_reviewed_at' => null,
        ];
    }
}
