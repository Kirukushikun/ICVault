<?php

namespace Tests\Feature;

use App\Enums\ImportStatus;
use App\Jobs\ParseImportBatch;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Services\AI\QuestionParserContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParseImportBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_parses_and_imports_a_batch_end_to_end(): void
    {
        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create([
            'category_id' => $category->id,
            'raw_content' => "First line here\nSecond line there",
            'status' => ImportStatus::Uploaded,
        ]);

        (new ParseImportBatch($batch->id))->handle(
            app(\App\Services\ImportPipelineService::class),
            app(QuestionParserContract::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Imported, $batch->status);
        $this->assertSame(2, $batch->questions()->count());
        $this->assertDatabaseHas('questions', ['category_id' => $category->id, 'answer' => 'here']);
    }

    public function test_a_parser_failure_marks_the_batch_failed_with_an_error_message(): void
    {
        $this->mock(QuestionParserContract::class)
            ->shouldReceive('parse')
            ->andThrow(new \RuntimeException('boom'));

        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Uploaded]);

        (new ParseImportBatch($batch->id))->handle(
            app(\App\Services\ImportPipelineService::class),
            app(QuestionParserContract::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Failed, $batch->status);
        $this->assertSame('boom', $batch->error_message);
    }
}
