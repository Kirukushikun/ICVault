<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Enums\MasteryState;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Services\MasteryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasteryServiceTest extends TestCase
{
    use RefreshDatabase;

    private MasteryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MasteryService;
    }

    public function test_new_question_with_one_correct_answer_advances_to_learning(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::New, 'mastery_streak' => 0]);

        $this->service->recordAttempt($question, correct: true);

        $this->assertSame(MasteryState::Learning, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_learning_question_needs_two_consecutive_correct_answers_to_advance_to_review(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Learning, 'mastery_streak' => 0]);

        $this->service->recordAttempt($question, correct: true);
        $this->assertSame(MasteryState::Learning, $question->mastery_state);
        $this->assertSame(1, $question->mastery_streak);

        $this->service->recordAttempt($question, correct: true);
        $this->assertSame(MasteryState::Review, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_review_question_needs_three_consecutive_correct_answers_to_advance_to_mastered(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Review, 'mastery_streak' => 0]);

        $this->service->recordAttempt($question, correct: true);
        $this->assertSame(MasteryState::Review, $question->mastery_state);
        $this->assertSame(1, $question->mastery_streak);

        $this->service->recordAttempt($question, correct: true);
        $this->assertSame(MasteryState::Review, $question->mastery_state);
        $this->assertSame(2, $question->mastery_streak);

        $this->service->recordAttempt($question, correct: true);
        $this->assertSame(MasteryState::Mastered, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_mastered_question_stays_mastered_and_keeps_counting_on_correct_answers(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Mastered, 'mastery_streak' => 5]);

        $this->service->recordAttempt($question, correct: true);

        $this->assertSame(MasteryState::Mastered, $question->mastery_state);
        $this->assertSame(6, $question->mastery_streak);
    }

    public function test_new_question_stays_new_on_wrong_answer(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::New, 'mastery_streak' => 0]);

        $this->service->recordAttempt($question, correct: false);

        $this->assertSame(MasteryState::New, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_learning_question_regresses_to_new_on_wrong_answer(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Learning, 'mastery_streak' => 1]);

        $this->service->recordAttempt($question, correct: false);

        $this->assertSame(MasteryState::New, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_review_question_regresses_to_learning_on_wrong_answer(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Review, 'mastery_streak' => 2]);

        $this->service->recordAttempt($question, correct: false);

        $this->assertSame(MasteryState::Learning, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_mastered_question_regresses_to_review_on_wrong_answer(): void
    {
        $question = Question::factory()->create(['mastery_state' => MasteryState::Mastered, 'mastery_streak' => 10]);

        $this->service->recordAttempt($question, correct: false);

        $this->assertSame(MasteryState::Review, $question->mastery_state);
        $this->assertSame(0, $question->mastery_streak);
    }

    public function test_recording_an_attempt_updates_last_reviewed_at(): void
    {
        $question = Question::factory()->create(['last_reviewed_at' => null]);

        $this->assertNull($question->last_reviewed_at);

        $this->service->recordAttempt($question, correct: true);

        $this->assertNotNull($question->fresh()->last_reviewed_at);
    }
}
