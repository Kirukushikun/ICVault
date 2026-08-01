<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /** @var class-string<Attempt> */
    protected $model = Attempt::class;

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
