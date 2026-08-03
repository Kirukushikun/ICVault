<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Livewire\ImportPage;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Services\AI\NaiveLineParser;
use App\Tools\Quiz\Services\AI\QuestionParserContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        // The queue runs sync in tests, so `generate` immediately runs the
        // real parser too. These tests assert on exact generated text, which
        // only the deterministic stand-in can promise — the real parser
        // calls out to Anthropic and belongs to ClaudeQuestionParserTest
        // instead.
        $this->app->bind(QuestionParserContract::class, NaiveLineParser::class);
    }

    public function test_submitting_a_session_log_reaches_ready_and_populates_candidates(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', "Middleware runs before the controller\nRoutes are registered in order")
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'log', 'status' => 'ready']);
        $this->assertSame(0, Question::count());

        $candidates = $component->get('candidates');
        $this->assertCount(2, $candidates);
        $this->assertSame('controller', $candidates[0]['answer']);
        $this->assertSame('order', $candidates[1]['answer']);
    }

    public function test_submitting_an_uploaded_note_file_reaches_ready_and_populates_candidates(): void
    {
        Storage::fake('local');
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'notes')
            ->set('categoryId', $category->id)
            ->set('noteFile', UploadedFile::fake()->createWithContent('note.md', "A note about facades\n"))
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'note', 'status' => 'ready']);
        $this->assertSame('facades', $component->get('candidates')[0]['answer']);
    }

    public function test_generating_without_a_category_fails_validation(): void
    {
        Category::query()->delete();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('sessionLogText', 'something happened')
            ->call('generate')
            ->assertHasErrors(['categoryId']);
    }

    public function test_generating_a_note_without_a_file_fails_validation(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'notes')
            ->set('categoryId', $category->id)
            ->call('generate')
            ->assertHasErrors(['noteFile']);
    }

    public function test_uploading_a_non_markdown_file_is_rejected(): void
    {
        Storage::fake('local');
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'notes')
            ->set('categoryId', $category->id)
            ->set('noteFile', UploadedFile::fake()->create('slides.pdf', 12))
            ->call('generate')
            ->assertHasErrors(['noteFile']);

        $this->assertDatabaseCount('import_batches', 0);
    }

    /**
     * Asserts the upload's bytes actually reach the batch, not merely that
     * some candidate came out the other end.
     *
     * Caveat worth knowing: this does NOT reproduce the real-world failure
     * where `storeAs()` is called before `get()`. `storeAs()` moves the temp
     * file when source and target disks match, so a later `get()` returns
     * null — but under `Storage::fake()` the move doesn't invalidate the
     * handle the same way, and this test still passes with the calls in the
     * wrong order (verified). The ordering is guarded by the comment in
     * `readMarkdownUploadOrText()`, not by this test.
     *
     * @param  'notes'|'markdown'  $tab
     */
    #[DataProvider('uploadTabs')]
    public function test_an_uploaded_files_contents_reach_the_batch_intact(string $tab, string $property, string $sourceType): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $category = Category::factory()->create();
        $body = "Q: What reaches the batch?\nAnswer: every byte of it";

        Livewire::test(ImportPage::class)
            ->set('tab', $tab)
            ->set('categoryId', $category->id)
            ->set($property, UploadedFile::fake()->createWithContent('note.md', $body))
            ->call('generate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('import_batches', [
            'source_type' => $sourceType,
            'raw_content' => $body,
        ]);
    }

    #[DataProvider('uploadTabs')]
    public function test_an_upload_that_reads_as_empty_is_reported_as_such(string $tab, string $property): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', $tab)
            ->set('categoryId', $category->id)
            ->set($property, UploadedFile::fake()->createWithContent('note.md', '   '))
            ->call('generate')
            ->assertHasErrors([$property]);

        $this->assertDatabaseCount('import_batches', 0);
    }

    /** @return array<string, array{string, string, string}> */
    public static function uploadTabs(): array
    {
        return [
            'ready-made' => ['markdown', 'markdownFile', 'markdown'],
            'study notes' => ['notes', 'noteFile', 'note'],
        ];
    }

    public function test_ai_candidates_carry_their_source_excerpt_and_manual_ones_do_not(): void
    {
        Http::preventStrayRequests();
        $category = Category::factory()->create();

        $ai = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'Middleware runs before the controller')
            ->call('generate')
            ->get('candidates')[0];

        $this->assertTrue($ai['isAi']);
        $this->assertSame('Middleware runs before the controller', $ai['excerpt']);

        $manual = Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->set('markdownText', "Q: x\nAnswer: y")
            ->call('generate')
            ->get('candidates')[0];

        $this->assertFalse($manual['isAi']);
        $this->assertNull($manual['excerpt']);
    }

    public function test_submitting_pasted_structured_markdown_reaches_ready_with_no_ai_call(): void
    {
        Http::preventStrayRequests();
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->set('markdownText', "Q: The ____ directive escapes output in Blade.\nAnswer: {{ }}")
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'markdown', 'status' => 'ready']);
        $this->assertCount(1, $component->get('candidates'));
    }

    public function test_submitting_an_uploaded_structured_markdown_file_reaches_ready(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->set('markdownFile', UploadedFile::fake()->createWithContent('quiz.md', "Q: x\nAnswer: y"))
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'markdown', 'status' => 'ready']);
        $this->assertCount(1, $component->get('candidates'));
    }

    public function test_markdown_tab_requires_either_a_file_or_pasted_text(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->call('generate')
            ->assertHasErrors(['markdownFile']);
    }

    public function test_markdown_tab_rejects_providing_both_a_file_and_pasted_text(): void
    {
        Storage::fake('local');
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->set('markdownFile', UploadedFile::fake()->createWithContent('q.md', "Q: x\nAnswer: y"))
            ->set('markdownText', 'Q: also here')
            ->call('generate')
            ->assertHasErrors(['markdownFile']);
    }

    public function test_ai_disabled_blocks_the_notes_and_session_tabs(): void
    {
        config(['services.anthropic.enabled' => false]);
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'something')
            ->call('generate')
            ->assertHasErrors(['tab']);

        $this->assertDatabaseCount('import_batches', 0);
    }

    public function test_ai_disabled_still_allows_the_markdown_tab(): void
    {
        config(['services.anthropic.enabled' => false]);
        Http::preventStrayRequests();
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'markdown')
            ->set('categoryId', $category->id)
            ->set('markdownText', "Q: x\nAnswer: y")
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'markdown', 'status' => 'ready']);
    }

    public function test_mount_defaults_to_the_markdown_tab_when_ai_is_disabled(): void
    {
        config(['services.anthropic.enabled' => false]);

        Livewire::test(ImportPage::class)->assertSet('tab', 'markdown');
    }

    public function test_save_candidates_commits_edited_rows_and_marks_the_batch_imported(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'First line here')
            ->call('generate');

        $component->set('candidates.0.prompt', 'An edited prompt ____')
            ->call('saveCandidates');

        $this->assertDatabaseHas('questions', [
            'category_id' => $category->id,
            'prompt' => 'An edited prompt ____',
            'answer' => 'here',
        ]);
        $this->assertDatabaseHas('import_batches', ['status' => 'imported']);
        $this->assertSame([], $component->get('candidates'));
    }

    public function test_discard_candidate_removes_it_with_no_db_write(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', "First line here\nSecond line there")
            ->call('generate');

        $this->assertCount(2, $component->get('candidates'));

        $component->call('discardCandidate', 0);

        $remaining = $component->get('candidates');
        $this->assertCount(1, $remaining);
        $this->assertSame('there', $remaining[0]['answer']);
        $this->assertSame(0, Question::count());
    }

    public function test_save_candidates_rejects_a_multiple_choice_row_with_no_option_marked_correct(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'First line here')
            ->call('generate');

        $component->set('candidates.0.type', 'multiple_choice')
            ->set('candidates.0.options', ['one', 'two', 'three'])
            ->set('candidates.0.answerIndex', null)
            ->call('saveCandidates')
            ->assertHasErrors(['candidates.0.options']);

        $this->assertSame(0, Question::count());
        $this->assertCount(1, $component->get('candidates'));
    }

    public function test_save_candidates_rejects_a_multiple_choice_row_with_fewer_than_two_options(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'First line here')
            ->call('generate')
            ->set('candidates.0.type', 'multiple_choice')
            ->set('candidates.0.options', ['only one', ''])
            ->set('candidates.0.answerIndex', 0)
            ->call('saveCandidates')
            ->assertHasErrors(['candidates.0.options']);

        $this->assertSame(0, Question::count());
    }

    /**
     * The correct answer is held by position, so editing the marked option's
     * own text has to follow it — this is the drift the old match-by-text
     * shape allowed and the reason `answerIndex` exists.
     */
    public function test_a_multiple_choice_answer_follows_the_marked_option_when_its_text_is_edited(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'First line here')
            ->call('generate')
            ->set('candidates.0.type', 'multiple_choice')
            ->set('candidates.0.options', ['first', 'second'])
            ->set('candidates.0.answerIndex', 0)
            ->set('candidates.0.options.0', 'corrected text')
            ->call('saveCandidates');

        $this->assertDatabaseHas('questions', [
            'answer' => 'corrected text',
            'type' => 'multiple_choice',
        ]);
    }

    public function test_removing_the_option_marked_correct_clears_the_mark(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', 'First line here')
            ->call('generate')
            ->set('candidates.0.type', 'multiple_choice')
            ->set('candidates.0.options', ['first', 'second', 'third'])
            ->call('markCorrect', 0, 2);

        $this->assertSame(2, $component->get('candidates.0.answerIndex'));

        $component->call('removeOption', 0, 2);
        $this->assertNull($component->get('candidates.0.answerIndex'));

        // Removing an option above the marked one shifts the mark down with it.
        $component->call('markCorrect', 0, 1)->call('removeOption', 0, 0);
        $this->assertSame(0, $component->get('candidates.0.answerIndex'));
    }

    public function test_saving_one_candidate_leaves_the_rest_staged_and_the_batch_open(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', "First line here\nSecond line there")
            ->call('generate')
            ->call('saveCandidate', 0);

        $this->assertSame(1, Question::count());
        $this->assertDatabaseHas('questions', ['answer' => 'here']);
        $this->assertCount(1, $component->get('candidates'));

        // Still open, because there's a candidate left to deal with.
        $this->assertDatabaseHas('import_batches', ['status' => 'ready']);

        $component->call('saveCandidate', 0);

        $this->assertSame(2, Question::count());
        $this->assertDatabaseHas('import_batches', ['status' => 'imported']);
    }

    public function test_saving_a_candidate_with_an_empty_prompt_is_rejected_without_touching_the_others(): void
    {
        $category = Category::factory()->create();

        $component = Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', "First line here\nSecond line there")
            ->call('generate')
            ->set('candidates.0.prompt', '   ')
            ->call('saveCandidate', 0)
            ->assertHasErrors(['candidates.0.prompt']);

        $this->assertSame(0, Question::count());
        $this->assertCount(2, $component->get('candidates'));
        $this->assertDatabaseHas('import_batches', ['status' => 'ready']);
    }

    public function test_visiting_the_page_with_a_ready_batch_in_the_url_resumes_the_review(): void
    {
        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create([
            'category_id' => $category->id,
            'status' => ImportStatus::Ready,
            'candidates_json' => [[
                'difficulty' => 'medium',
                'type' => 'fill_blank',
                'prompt' => 'Resumed prompt ____',
                'options_json' => null,
                'answer' => 'value',
                'explanation' => null,
            ]],
        ]);

        $response = $this->get(route('quiz.import', ['batch' => $batch->id]));

        $response->assertOk()->assertSee('Resumed prompt');
    }
}
