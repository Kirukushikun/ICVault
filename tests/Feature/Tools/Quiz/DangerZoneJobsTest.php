<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Jobs\ResetMasteryProgress;
use App\Tools\Quiz\Jobs\ResetQuestionPool;
use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DangerZoneJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_question_pool_deletes_every_question_and_its_attempts(): void
    {
        $question = Question::factory()->fillBlank('a')->create();
        $session = QuizSession::factory()->create();
        Attempt::factory()->create(['question_id' => $question->id, 'quiz_session_id' => $session->id]);

        (new ResetQuestionPool)->handle();

        $this->assertSame(0, Question::count());
        $this->assertSame(0, Attempt::count());
    }

    public function test_reset_mastery_progress_resets_questions_and_clears_history_without_deleting_questions(): void
    {
        $question = Question::factory()->fillBlank('a')->create([
            'mastery_state' => 'mastered',
            'mastery_streak' => 3,
            'last_reviewed_at' => now(),
        ]);
        $session = QuizSession::factory()->create();
        Attempt::factory()->create(['question_id' => $question->id, 'quiz_session_id' => $session->id]);

        (new ResetMasteryProgress)->handle();

        $this->assertSame(1, Question::count());
        $question->refresh();
        $this->assertSame('new', $question->mastery_state->value);
        $this->assertSame(0, $question->mastery_streak);
        $this->assertNull($question->last_reviewed_at);
        $this->assertSame(0, Attempt::count());
        $this->assertSame(0, QuizSession::count());
    }
}
