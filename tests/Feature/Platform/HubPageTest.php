<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Platform\Livewire\Hub\HubPage;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HubPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_lists_every_enabled_tool(): void
    {
        Livewire::test(HubPage::class)
            ->assertSee('Quiz Vault')
            ->assertSee('Concept Visualizer');
    }

    public function test_it_shows_a_tools_own_summary_line(): void
    {
        Question::factory()->for(Category::factory())->count(3)->create();

        Livewire::test(HubPage::class)->assertSee('3 cards');
    }

    public function test_it_prompts_to_import_when_the_pool_is_empty(): void
    {
        Livewire::test(HubPage::class)->assertSee('No questions yet');
    }

    /**
     * The Hub must not start a session just by being looked at — that would
     * silently consume the day's quota row.
     */
    public function test_rendering_the_hub_does_not_create_todays_quiz_session(): void
    {
        Question::factory()->for(Category::factory())->create();

        Livewire::test(HubPage::class)->assertOk();

        $this->assertSame(0, QuizSession::count());
    }

    public function test_it_shows_an_empty_state_when_no_tool_reports_activity(): void
    {
        Livewire::test(HubPage::class)->assertSee('Nothing yet');
    }

    public function test_it_merges_activity_contributed_by_tools(): void
    {
        $category = Category::factory()->create();

        $batch = ImportBatch::factory()->for($category)->create();
        Question::factory()->for($category)->count(2)->create(['import_batch_id' => $batch->id]);

        QuizSession::factory()->create(['completed_count' => 5, 'quota' => 8]);

        Livewire::test(HubPage::class)
            ->assertSee('Imported 2 new questions')
            ->assertSee('Answered 5 of 8')
            ->assertDontSee('Nothing yet');
    }
}
