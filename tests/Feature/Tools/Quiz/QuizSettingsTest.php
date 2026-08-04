<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Livewire\QuizSettings;
use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizPreference;
use App\Tools\Quiz\Models\QuizSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuizSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_reports_real_pool_stats(): void
    {
        $category = Category::factory()->create();
        Question::factory()->fillBlank('a')->for($category)->create();
        $session = QuizSession::factory()->create();
        $question = Question::factory()->fillBlank('b')->for($category)->create();
        Attempt::factory()->create(['quiz_session_id' => $session->id, 'question_id' => $question->id, 'correct' => true]);
        Attempt::factory()->create(['quiz_session_id' => $session->id, 'question_id' => $question->id, 'correct' => false]);

        Livewire::test(QuizSettings::class)
            ->assertViewHas('questionCount', 2)
            ->assertViewHas('categoryCount', 1)
            ->assertViewHas('sessionCount', 1)
            ->assertViewHas('avgRecall', 50);
    }

    public function test_export_json_streams_a_download_of_the_question_pool(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Question::factory()->fillBlank('job')->for($category)->create(['prompt' => 'dispatch() queues a ____']);

        $response = Livewire::test(QuizSettings::class)->call('exportJson');

        $response->assertStatus(200);
    }

    public function test_export_csv_streams_a_download_of_the_question_pool(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Question::factory()->fillBlank('job')->for($category)->create(['prompt' => 'dispatch() queues a ____']);

        Livewire::test(QuizSettings::class)->call('exportCsv')->assertStatus(200);
    }

    public function test_export_markdown_streams_a_download_of_the_question_pool(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Question::factory()->fillBlank('job')->for($category)->create(['prompt' => 'dispatch() queues a ____']);

        Livewire::test(QuizSettings::class)->call('exportMarkdown')->assertStatus(200);
    }

    public function test_import_merges_questions_from_a_json_export(): void
    {
        Storage::fake('local');

        $payload = json_encode([
            'exported_at' => now()->toIso8601String(),
            'questions' => [
                [
                    'category_slug' => 'sql',
                    'category_name' => 'SQL',
                    'category_color' => '#333333',
                    'difficulty' => 'hard',
                    'type' => 'fill_blank',
                    'prompt' => 'Write a query to find duplicate ____',
                    'options' => null,
                    'answer' => 'emails',
                    'explanation' => null,
                ],
            ],
        ]);

        Livewire::test(QuizSettings::class)
            ->set('importMode', 'merge')
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', $payload))
            ->call('runImport');

        $this->assertDatabaseHas('categories', ['slug' => 'sql']);
        $this->assertDatabaseHas('questions', ['prompt' => 'Write a query to find duplicate ____', 'answer' => 'emails']);
    }

    public function test_import_skips_duplicates_when_merging(): void
    {
        Storage::fake('local');

        $category = Category::factory()->create(['slug' => 'git']);
        Question::factory()->fillBlank('rewrites')->for($category)->create(['prompt' => 'git rebase vs merge']);

        $payload = json_encode([
            'questions' => [[
                'category_slug' => 'git',
                'category_name' => 'Git',
                'difficulty' => 'easy',
                'type' => 'fill_blank',
                'prompt' => 'git rebase vs merge',
                'options' => null,
                'answer' => 'rewrites',
                'explanation' => null,
            ]],
        ]);

        Livewire::test(QuizSettings::class)
            ->set('importMode', 'merge')
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', $payload))
            ->call('runImport');

        $this->assertSame(1, Question::where('prompt', 'git rebase vs merge')->count());
    }

    public function test_import_replace_all_clears_existing_questions_first(): void
    {
        Storage::fake('local');

        Question::factory()->fillBlank('old')->create(['prompt' => 'stale question']);

        $payload = json_encode([
            'questions' => [[
                'category_slug' => 'js-fundamentals',
                'category_name' => 'JS Fundamentals',
                'difficulty' => 'medium',
                'type' => 'fill_blank',
                'prompt' => 'fresh question',
                'options' => null,
                'answer' => 'new',
                'explanation' => null,
            ]],
        ]);

        Livewire::test(QuizSettings::class)
            ->set('importMode', 'replace')
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', $payload))
            ->call('runImport');

        $this->assertDatabaseMissing('questions', ['prompt' => 'stale question']);
        $this->assertDatabaseHas('questions', ['prompt' => 'fresh question']);
    }

    public function test_import_rejects_a_file_that_is_not_a_valid_export(): void
    {
        Storage::fake('local');

        Livewire::test(QuizSettings::class)
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', json_encode(['nope' => true])))
            ->call('runImport')
            ->assertHasErrors(['importFile']);
    }

    public function test_delete_all_requires_exact_confirmation_text(): void
    {
        Question::factory()->fillBlank('a')->create();

        Livewire::test(QuizSettings::class)
            ->call('startDeleteConfirm')
            ->set('deleteConfirmText', 'delete')
            ->call('confirmDelete')
            ->assertHasErrors(['deleteConfirmText']);

        $this->assertSame(1, Question::count());
    }

    public function test_delete_all_dispatches_the_reset_question_pool_job_once_confirmed(): void
    {
        Question::factory()->fillBlank('a')->create();

        Livewire::test(QuizSettings::class)
            ->call('startDeleteConfirm')
            ->set('deleteConfirmText', 'DELETE')
            ->call('confirmDelete')
            ->assertSet('confirmingDelete', false);

        $this->assertSame(0, Question::count());
    }

    public function test_clear_progress_requires_exact_confirmation_text(): void
    {
        $session = QuizSession::factory()->create();

        Livewire::test(QuizSettings::class)
            ->call('startClearConfirm')
            ->set('clearConfirmText', 'reset')
            ->call('confirmClear')
            ->assertHasErrors(['clearConfirmText']);

        $this->assertSame(1, QuizSession::count());
    }

    public function test_clear_progress_dispatches_the_reset_mastery_progress_job_once_confirmed(): void
    {
        QuizSession::factory()->create();

        Livewire::test(QuizSettings::class)
            ->call('startClearConfirm')
            ->set('clearConfirmText', 'RESET')
            ->call('confirmClear')
            ->assertSet('confirmingClear', false);

        $this->assertSame(0, QuizSession::count());
    }

    public function test_it_adds_a_category(): void
    {
        Livewire::test(QuizSettings::class)
            ->set('newCategoryName', 'Docker')
            ->set('newCategoryColor', '#1a5f7a')
            ->call('addCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Docker', 'slug' => 'docker', 'color' => '#1a5f7a']);
    }

    public function test_it_rejects_a_duplicate_category_name(): void
    {
        Category::factory()->create(['name' => 'Docker']);

        Livewire::test(QuizSettings::class)
            ->set('newCategoryName', 'Docker')
            ->call('addCategory')
            ->assertHasErrors(['newCategoryName']);
    }

    public function test_it_deletes_a_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(QuizSettings::class)->call('deleteCategory', $category->id);

        $this->assertModelMissing($category);
    }

    /** Categories cascade-delete their questions — see the questions table migration. */
    public function test_deleting_a_category_also_removes_its_questions(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->for($category)->create();

        Livewire::test(QuizSettings::class)->call('deleteCategory', $category->id);

        $this->assertModelMissing($question);
    }

    public function test_changing_a_preference_persists_it_immediately(): void
    {
        Livewire::test(QuizSettings::class)
            ->set('prefShuffleOrder', false)
            ->set('prefDailyQuota', 12);

        $pref = QuizPreference::current();
        $this->assertFalse($pref->shuffle_order);
        $this->assertSame(12, $pref->daily_quota);
    }

    public function test_reset_streak_moves_the_streak_cutoff_to_now(): void
    {
        Livewire::test(QuizSettings::class)->call('resetStreak');

        $this->assertNotNull(QuizPreference::current()->streak_broken_at);
    }
}
