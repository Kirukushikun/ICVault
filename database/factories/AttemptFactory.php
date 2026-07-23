<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'quiz_session_id' => QuizSession::factory(),
            'correct' => $this->faker->boolean(),
            'answered_at' => now(),
        ];
    }
}
