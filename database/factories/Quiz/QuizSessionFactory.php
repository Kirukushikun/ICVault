<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizSession>
 */
class QuizSessionFactory extends Factory
{
    /** @var class-string<QuizSession> */
    protected $model = QuizSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => today(),
            'quota' => 8,
            'completed_count' => 0,
        ];
    }
}
