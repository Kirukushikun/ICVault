<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\ImportPipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ImportPipelineServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImportPipelineService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ImportPipelineService;
    }

    public function test_it_walks_the_batch_through_each_allowed_transition(): void
    {
        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Uploaded]);

        $this->service->transitionTo($batch, ImportStatus::Parsed);
        $this->assertSame(ImportStatus::Parsed, $batch->fresh()->status);

        $this->service->transitionTo($batch, ImportStatus::AiConverted);
        $this->assertSame(ImportStatus::AiConverted, $batch->fresh()->status);

        $this->service->transitionTo($batch, ImportStatus::Queued);
        $this->assertSame(ImportStatus::Queued, $batch->fresh()->status);
    }

    public function test_it_rejects_a_transition_that_skips_a_stage(): void
    {
        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Uploaded]);

        $this->expectException(RuntimeException::class);

        $this->service->transitionTo($batch, ImportStatus::Queued);
    }

    public function test_it_rejects_a_transition_from_a_terminal_state(): void
    {
        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Imported]);

        $this->expectException(RuntimeException::class);

        $this->service->transitionTo($batch, ImportStatus::Parsed);
    }

    public function test_import_questions_creates_rows_tied_to_the_batch_and_its_category_then_marks_imported(): void
    {
        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Queued, 'category_id' => $category->id]);

        $questions = $this->service->importQuestions($batch, [
            [
                'difficulty' => 'medium',
                'type' => 'fill_blank',
                'prompt' => 'Fill in the blank: dispatch() queues a ____',
                'options_json' => null,
                'answer' => 'job',
                'explanation' => 'dispatch() queues a job',
            ],
        ]);

        $this->assertCount(1, $questions);
        $this->assertDatabaseHas('questions', [
            'category_id' => $category->id,
            'import_batch_id' => $batch->id,
            'answer' => 'job',
        ]);
        $this->assertSame(ImportStatus::Imported, $batch->fresh()->status);
    }

    public function test_import_questions_spins_a_tip_off_each_question_with_an_explanation(): void
    {
        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Queued, 'category_id' => $category->id]);

        $questions = $this->service->importQuestions($batch, [
            [
                'difficulty' => 'medium',
                'type' => 'fill_blank',
                'prompt' => 'Fill in the blank: dispatch() queues a ____',
                'options_json' => null,
                'answer' => 'job',
                'explanation' => 'dispatch() queues a job',
            ],
            [
                'difficulty' => 'medium',
                'type' => 'fill_blank',
                'prompt' => 'No explanation here',
                'options_json' => null,
                'answer' => 'nothing',
                'explanation' => null,
            ],
        ]);

        $this->assertDatabaseHas('tips', [
            'category_id' => $category->id,
            'body' => 'dispatch() queues a job',
            'source_question_id' => $questions->first()->id,
        ]);
        $this->assertSame(1, \App\Tools\Quiz\Models\Tip::count());
    }
}
