<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Livewire\QuizDashboard;
use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizSession;
use App\Tools\Quiz\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_falls_back_to_any_tip_before_the_first_attempt_of_the_day(): void
    {
        $tip = Tip::factory()->create();

        Livewire::test(QuizDashboard::class)
            ->assertViewHas('tip', fn (?Tip $shown) => $shown?->id === $tip->id);
    }

    public function test_it_shows_no_tip_when_none_exist(): void
    {
        Livewire::test(QuizDashboard::class)
            ->assertViewHas('tip', null);
    }

    public function test_it_prefers_a_tip_from_a_category_touched_by_todays_session(): void
    {
        $matchingCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $matchingTip = Tip::factory()->create(['category_id' => $matchingCategory->id]);
        Tip::factory()->create(['category_id' => $otherCategory->id]);

        $question = Question::factory()->for($matchingCategory)->create();
        $session = QuizSession::today();
        Attempt::factory()->create(['question_id' => $question->id, 'quiz_session_id' => $session->id]);

        Livewire::test(QuizDashboard::class)
            ->assertViewHas('tip', fn (?Tip $shown) => $shown?->id === $matchingTip->id);
    }
}
