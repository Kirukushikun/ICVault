<?php

namespace Tests\Feature;

use App\Livewire\Settings\SettingsPage;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
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

        Livewire::test(SettingsPage::class)
            ->assertViewHas('questionCount', 2)
            ->assertViewHas('categoryCount', 1)
            ->assertViewHas('sessionCount', 1)
            ->assertViewHas('avgRecall', 50);
    }

    public function test_export_json_streams_a_download_of_the_question_pool(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Question::factory()->fillBlank('job')->for($category)->create(['prompt' => 'dispatch() queues a ____']);

        $response = Livewire::test(SettingsPage::class)->call('exportJson');

        $response->assertStatus(200);
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

        Livewire::test(SettingsPage::class)
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

        Livewire::test(SettingsPage::class)
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

        Livewire::test(SettingsPage::class)
            ->set('importMode', 'replace')
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', $payload))
            ->call('runImport');

        $this->assertDatabaseMissing('questions', ['prompt' => 'stale question']);
        $this->assertDatabaseHas('questions', ['prompt' => 'fresh question']);
    }

    public function test_import_rejects_a_file_that_is_not_a_valid_export(): void
    {
        Storage::fake('local');

        Livewire::test(SettingsPage::class)
            ->set('importFile', UploadedFile::fake()->createWithContent('export.json', json_encode(['nope' => true])))
            ->call('runImport')
            ->assertHasErrors(['importFile']);
    }
}
