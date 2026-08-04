<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Enums\MasteryState;
use App\Tools\Quiz\Livewire\QuizDashboard;
use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizPreference;
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

    /** The dashboard used to show a hardcoded fake category grid — this pins it to real pool data. */
    public function test_it_reports_real_category_counts_and_mastery(): void
    {
        $category = Category::factory()->create(['name' => 'Laravel']);
        Question::factory()->for($category)->create(['mastery_state' => MasteryState::Mastered]);
        Question::factory()->for($category)->create(['mastery_state' => MasteryState::New]);

        Livewire::test(QuizDashboard::class)
            ->assertViewHas('poolCount', 2)
            ->assertViewHas('categories', fn ($categories) => $categories === [
                ['name' => 'Laravel', 'count' => 2, 'pct' => 50],
            ]);
    }

    public function test_streak_counts_consecutive_days_with_quota_met(): void
    {
        QuizSession::factory()->create(['date' => today()->subDays(2), 'quota' => 5, 'completed_count' => 5]);
        QuizSession::factory()->create(['date' => today()->subDay(), 'quota' => 5, 'completed_count' => 5]);

        Livewire::test(QuizDashboard::class)->assertViewHas('streak', 2);
    }

    public function test_streak_breaks_on_a_gap_day(): void
    {
        QuizSession::factory()->create(['date' => today()->subDays(3), 'quota' => 5, 'completed_count' => 5]);
        // Gap at subDays(2) — unmet.
        QuizSession::factory()->create(['date' => today()->subDay(), 'quota' => 5, 'completed_count' => 5]);

        Livewire::test(QuizDashboard::class)->assertViewHas('streak', 1);
    }

    public function test_reset_streak_stops_counting_sessions_before_the_reset(): void
    {
        QuizSession::factory()->create(['date' => today()->subDay(), 'quota' => 5, 'completed_count' => 5]);
        QuizPreference::current()->update(['streak_broken_at' => now()]);

        Livewire::test(QuizDashboard::class)->assertViewHas('streak', 0);
    }
}
