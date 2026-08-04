<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Services\MarkdownQuestionParser;
use App\Tools\Quiz\Services\PoolExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoolExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_includes_a_header_row_and_one_row_per_question(): void
    {
        $category = Category::factory()->create(['name' => 'Laravel']);
        Question::factory()->for($category)->fillBlank('dispatch')->create(['prompt' => 'The ____ helper queues a job.']);

        $csv = app(PoolExportService::class)->toCsv();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(['category', 'difficulty', 'type', 'prompt', 'options', 'answer', 'explanation'], $rows[0]);
        $this->assertSame('The ____ helper queues a job.', $rows[1][3]);
    }

    /**
     * The Markdown export exists specifically so it can be re-imported through
     * the Ready-made tab — this pins that round trip rather than just
     * checking the export renders without error.
     */
    public function test_markdown_export_round_trips_through_the_markdown_parser(): void
    {
        $category = Category::factory()->create();
        Question::factory()->for($category)->multipleChoice(['wrong', 'right'], 'right')
            ->create(['prompt' => 'Pick the correct one', 'explanation' => 'because reasons']);
        Question::factory()->for($category)->fillBlank('answer')
            ->create(['prompt' => 'Fill this ____ in']);

        $markdown = app(PoolExportService::class)->toMarkdown();
        $reparsed = (new MarkdownQuestionParser)->parse($markdown);

        $this->assertCount(2, $reparsed);
        $this->assertSame('Pick the correct one', $reparsed[0]['prompt']);
        $this->assertSame('right', $reparsed[0]['answer']);
        $this->assertSame('Fill this ____ in', $reparsed[1]['prompt']);
        $this->assertSame('answer', $reparsed[1]['answer']);
    }
}
