<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\AI\ClaudeQuestionParser;
use App\Tools\Quiz\Services\MarkdownQuestionParser;
use App\Tools\Quiz\Services\QuestionParserResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QuestionParserResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_source_type_resolves_to_the_markdown_parser(): void
    {
        $batch = ImportBatch::factory()->create(['source_type' => 'markdown']);

        $parser = (new QuestionParserResolver)->resolve($batch);

        $this->assertInstanceOf(MarkdownQuestionParser::class, $parser);
    }

    #[DataProvider('aiSourceTypes')]
    public function test_ai_source_types_resolve_to_the_container_bound_parser(string $sourceType): void
    {
        $batch = ImportBatch::factory()->create(['source_type' => $sourceType]);

        $parser = (new QuestionParserResolver)->resolve($batch);

        $this->assertInstanceOf(ClaudeQuestionParser::class, $parser);
    }

    /** @return array<string, array{string}> */
    public static function aiSourceTypes(): array
    {
        return ['note' => ['note'], 'log' => ['log']];
    }
}
