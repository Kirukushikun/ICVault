<?php

namespace Tests\Feature;

use App\Enums\MasteryState;
use App\Livewire\Quiz\QuizSession;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizSession as QuizSessionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_selecting_a_mode_builds_a_pool_filtered_by_type(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->multipleChoice(['a', 'b'], 'a')->count(2)->create();
        Question::factory()->for($category)->fillBlank('foo')->count(3)->create();

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'mc')
            ->call('start')
            ->assertSet('screen', 'active')
            ->assertCount('pool', 2);
    }

    public function test_shuffle_mode_includes_every_question_type(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->multipleChoice(['a', 'b'], 'a')->create();
        Question::factory()->for($category)->fillBlank('foo')->create();
        Question::factory()->for($category)->code('bar')->create();

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'shuffle')
            ->call('start')
            ->assertCount('pool', 3);
    }

    public function test_correct_multiple_choice_answer_logs_a_correct_attempt_and_advances_mastery(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->for($category)
            ->multipleChoice(['wrong', 'right'], 'right')
            ->create(['mastery_state' => MasteryState::New, 'mastery_streak' => 0]);

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'mc')
            ->call('start')
            ->call('selectOption', 1)
            ->call('submit')
            ->assertSet('revealed', true)
            ->assertSet('lastCorrect', true);

        $this->assertDatabaseHas('attempts', ['question_id' => $question->id, 'correct' => true]);
        $this->assertSame(MasteryState::Learning, $question->fresh()->mastery_state);
    }

    public function test_incorrect_fill_blank_answer_logs_an_incorrect_attempt(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->for($category)
            ->fillBlank('inside')
            ->create(['mastery_state' => MasteryState::Review, 'mastery_streak' => 1]);

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'fill')
            ->call('start')
            ->set('fillValue', 'outside')
            ->call('submit')
            ->assertSet('lastCorrect', false);

        $this->assertDatabaseHas('attempts', ['question_id' => $question->id, 'correct' => false]);
        $this->assertSame(MasteryState::Learning, $question->fresh()->mastery_state);
    }

    public function test_fill_blank_answer_is_matched_case_insensitively_and_trimmed(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->fillBlank('Inside')->create();

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'fill')
            ->call('start')
            ->set('fillValue', '  inside  ')
            ->call('submit')
            ->assertSet('lastCorrect', true);
    }

    public function test_code_question_submission_does_not_log_an_attempt_or_change_mastery(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->for($category)->code('reference answer')
            ->create(['mastery_state' => MasteryState::New, 'mastery_streak' => 0]);

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'code')
            ->call('start')
            ->call('submit')
            ->assertSet('revealed', true);

        $this->assertSame(0, Attempt::count());
        $this->assertSame(MasteryState::New, $question->fresh()->mastery_state);
    }

    public function test_submitting_increments_todays_quiz_session_completed_count(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->fillBlank('inside')->create();

        $session = QuizSessionModel::today();
        $this->assertSame(0, $session->completed_count);

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'fill')
            ->call('start')
            ->set('fillValue', 'inside')
            ->call('submit');

        $this->assertSame(1, $session->fresh()->completed_count);
    }

    public function test_skip_advances_to_the_next_question_without_logging_an_attempt(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->fillBlank('a')->create();
        Question::factory()->for($category)->fillBlank('b')->create();

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'fill')
            ->call('start')
            ->assertSet('index', 0)
            ->call('skip')
            ->assertSet('index', 1)
            ->assertSet('revealed', false);

        $this->assertSame(0, Attempt::count());
    }

    public function test_exit_returns_to_the_setup_screen(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->fillBlank('a')->create();

        Livewire::test(QuizSession::class)
            ->call('selectMode', 'fill')
            ->call('start')
            ->assertSet('screen', 'active')
            ->call('exit')
            ->assertSet('screen', 'setup')
            ->assertSet('mode', null);
    }
}
