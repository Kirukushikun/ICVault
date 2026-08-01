<?php

namespace App\Tools\Quiz\Livewire;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Jobs\ParseImportBatch;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['title' => 'ICVault — Import & Logs'])]
class ImportPage extends Component
{
    use WithFileUploads;

    public string $tab = 'notes';

    public $noteFile = null;

    public string $sessionLogText = '';

    public ?int $categoryId = null;

    public ?int $lastBatchId = null;

    public function mount(): void
    {
        $this->categoryId = Category::query()->orderBy('name')->value('id');
    }

    public function generate(): void
    {
        $this->validate(['categoryId' => ['required', 'exists:categories,id']]);

        if ($this->tab === 'notes') {
            $this->validate(['noteFile' => ['required', 'file', 'extensions:md', 'max:2048']]);
            $rawContent = $this->noteFile->get();
            $this->noteFile->storeAs('imports', $this->noteFile->getClientOriginalName());
            $sourceType = 'note';
        } else {
            $this->validate(['sessionLogText' => ['required', 'string']]);
            $rawContent = $this->sessionLogText;
            $sourceType = 'log';
        }

        $batch = ImportBatch::create([
            'source_type' => $sourceType,
            'category_id' => $this->categoryId,
            'raw_content' => $rawContent,
            'status' => ImportStatus::Uploaded,
        ]);

        ParseImportBatch::dispatch($batch->id);

        $this->lastBatchId = $batch->id;
        $this->reset(['noteFile', 'sessionLogText']);

        $batch->refresh();

        $this->dispatch(
            'toast',
            message: $batch->status === ImportStatus::Imported
                ? "✓ Generated {$batch->questions()->count()} question(s)"
                : '⚠ Import failed — check the batch status below',
        );
    }

    public function render()
    {
        $lastBatch = $this->lastBatchId
            ? ImportBatch::with('questions.category')->find($this->lastBatchId)
            : null;

        return view('tools.quiz.import', [
            'categories' => Category::query()->orderBy('name')->get(),
            'lastBatch' => $lastBatch,
            'generatedQuestions' => $lastBatch?->questions ?? collect(),
        ]);
    }
}
