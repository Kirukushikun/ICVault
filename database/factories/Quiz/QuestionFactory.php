<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Enums\MasteryState;
use App\Tools\Quiz\Enums\QuestionType;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /** @var class-string<Question> */
    protected $model = Question::class;

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
            'explanation' => $this->faker->sentence(),
            'mastery_state' => MasteryState::New,
            'mastery_streak' => 0,
            'last_reviewed_at' => null,
        ];
    }

    public function multipleChoice(array $options, string $correctAnswer): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::MultipleChoice,
            'options_json' => $options,
            'answer' => $correctAnswer,
        ]);
    }

    public function fillBlank(string $correctAnswer): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::FillBlank,
            'options_json' => null,
            'answer' => $correctAnswer,
        ]);
    }

    public function code(string $referenceAnswer): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Code,
            'options_json' => null,
            'answer' => $referenceAnswer,
        ]);
    }
}
