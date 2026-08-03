<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Jobs\ParseImportBatch;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\AI\NaiveLineParser;
use App\Tools\Quiz\Services\AI\QuestionParserContract;
use App\Tools\Quiz\Services\ImportPipelineService;
use App\Tools\Quiz\Services\QuestionParserResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ParseImportBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_parses_and_stages_candidates_for_review(): void
    {
        // The real parser calls out to Anthropic; this test is about the
        // pipeline wiring, so it's fed the deterministic stand-in explicitly
        // rather than whatever parser happens to be bound in the container.
        $this->app->bind(QuestionParserContract::class, NaiveLineParser::class);

        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create([
            'category_id' => $category->id,
            'raw_content' => "First line here\nSecond line there",
            'status' => ImportStatus::Uploaded,
        ]);

        (new ParseImportBatch($batch->id))->handle(
            app(ImportPipelineService::class),
            app(QuestionParserResolver::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Ready, $batch->status);
        // Staged for review only — nothing is a real Question row yet.
        $this->assertSame(0, $batch->questions()->count());
        $this->assertCount(2, $batch->candidates_json);
        $this->assertSame('here', $batch->candidates_json[0]['answer']);
    }

    public function test_a_parser_failure_marks_the_batch_failed_with_an_error_message(): void
    {
        $this->mock(QuestionParserContract::class)
            ->shouldReceive('parse')
            ->andThrow(new \RuntimeException('boom'));

        $batch = ImportBatch::factory()->create(['status' => ImportStatus::Uploaded]);

        (new ParseImportBatch($batch->id))->handle(
            app(ImportPipelineService::class),
            app(QuestionParserResolver::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Failed, $batch->status);
        $this->assertSame('boom', $batch->error_message);
    }

    /**
     * Proof that running out of Anthropic credits doesn't break the app: the
     * failure surfaces as a normal Failed batch with a message the reader can
     * act on, not an unhandled exception. Goes through the real bound parser
     * (not the deterministic stand-in) since the whole point is the wiring
     * between the real HTTP failure and the job's catch block.
     */
    public function test_running_out_of_credits_fails_the_batch_instead_of_breaking_the_import(): void
    {
        config(['services.anthropic.key' => 'sk-ant-test', 'services.anthropic.enabled' => true]);
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => ['message' => 'Your credit balance is too low']], 402),
        ]);

        $batch = ImportBatch::factory()->create([
            'raw_content' => 'Some notes',
            'status' => ImportStatus::Uploaded,
        ]);

        (new ParseImportBatch($batch->id))->handle(
            app(ImportPipelineService::class),
            app(QuestionParserResolver::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Failed, $batch->status);
        $this->assertStringContainsString('billing needs attention', $batch->error_message);
        $this->assertSame(0, $batch->questions()->count());
    }

    public function test_a_markdown_batch_resolves_to_the_deterministic_parser_with_no_network_call(): void
    {
        Http::preventStrayRequests();

        $category = Category::factory()->create();
        $batch = ImportBatch::factory()->create([
            'category_id' => $category->id,
            'source_type' => 'markdown',
            'raw_content' => "Q: The ____ directive escapes output in Blade.\nAnswer: {{ }}",
            'status' => ImportStatus::Uploaded,
        ]);

        (new ParseImportBatch($batch->id))->handle(
            app(ImportPipelineService::class),
            app(QuestionParserResolver::class),
        );

        $batch->refresh();

        $this->assertSame(ImportStatus::Ready, $batch->status);
        $this->assertCount(1, $batch->candidates_json);
        $this->assertSame('fill_blank', $batch->candidates_json[0]['type']);
    }
}
