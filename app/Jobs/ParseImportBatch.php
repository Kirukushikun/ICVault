<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\ImportBatch;
use App\Services\AI\QuestionParserContract;
use App\Services\ImportPipelineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ParseImportBatch implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importBatchId) {}

    public function handle(ImportPipelineService $pipeline, QuestionParserContract $parser): void
    {
        $batch = ImportBatch::findOrFail($this->importBatchId);

        try {
            $pipeline->transitionTo($batch, ImportStatus::Parsed);

            $candidates = $parser->parse($batch->raw_content);

            $pipeline->transitionTo($batch, ImportStatus::AiConverted);
            $pipeline->transitionTo($batch, ImportStatus::Queued);

            $pipeline->importQuestions($batch, $candidates);
        } catch (Throwable $e) {
            $batch->update(['status' => ImportStatus::Failed, 'error_message' => $e->getMessage()]);
        }
    }
}
