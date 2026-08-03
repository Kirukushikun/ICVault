<?php

namespace App\Tools\Quiz\Jobs;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\ImportPipelineService;
use App\Tools\Quiz\Services\QuestionParserResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ParseImportBatch implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importBatchId) {}

    /**
     * Stops at `Ready` rather than auto-importing — candidates sit staged on
     * the batch until a reader reviews and saves them (see ImportPage).
     */
    public function handle(ImportPipelineService $pipeline, QuestionParserResolver $resolver): void
    {
        $batch = ImportBatch::findOrFail($this->importBatchId);

        try {
            $pipeline->transitionTo($batch, ImportStatus::Parsed);

            $parser = $resolver->resolve($batch);
            $candidates = $parser->parse($batch->raw_content);

            $pipeline->stageCandidates($batch, $candidates);
        } catch (Throwable $e) {
            $batch->update(['status' => ImportStatus::Failed, 'error_message' => $e->getMessage()]);
        }
    }
}
