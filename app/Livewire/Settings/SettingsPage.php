<?php

namespace App\Livewire\Settings;

use App\Jobs\ResetMasteryProgress;
use App\Jobs\ResetQuestionPool;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizSession;
use App\Services\PoolExportService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['title' => 'ICVault — Settings'])]
class SettingsPage extends Component
{
    use WithFileUploads;

    public $importFile;

    public string $importMode = 'merge';

    public bool $confirmingDelete = false;

    public string $deleteConfirmText = '';

    public bool $confirmingClear = false;

    public string $clearConfirmText = '';

    public function exportJson()
    {
        $payload = app(PoolExportService::class)->toJson();

        return response()->streamDownload(
            fn () => print($payload),
            'icvault-export-'.now()->format('Y-m-d_His').'.json',
            ['Content-Type' => 'application/json']
        );
    }

    public function runImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'extensions:json'],
            'importMode' => ['required', 'in:merge,replace'],
        ]);

        $decoded = json_decode(file_get_contents($this->importFile->getRealPath()), true);

        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            $this->addError('importFile', 'That file does not look like an ICVault export.');

            return;
        }

        $imported = DB::transaction(function () use ($decoded) {
            if ($this->importMode === 'replace') {
                Question::query()->delete();
            }

            $count = 0;

            foreach ($decoded['questions'] as $row) {
                if (empty($row['prompt']) || empty($row['answer']) || empty($row['difficulty']) || empty($row['type'])) {
                    continue;
                }

                if (empty($row['category_slug'])) {
                    continue;
                }

                $category = Category::firstOrCreate(
                    ['slug' => $row['category_slug']],
                    [
                        'name' => $row['category_name'] ?? ucfirst($row['category_slug']),
                        'color' => $row['category_color'] ?? '#666666',
                    ]
                );

                $isDuplicate = $this->importMode === 'merge' && Question::where('prompt', $row['prompt'])
                    ->where('category_id', $category?->id)
                    ->exists();

                if ($isDuplicate) {
                    continue;
                }

                Question::create([
                    'category_id' => $category?->id,
                    'difficulty' => $row['difficulty'],
                    'type' => $row['type'],
                    'prompt' => $row['prompt'],
                    'options_json' => $row['options'] ?? null,
                    'answer' => $row['answer'],
                    'explanation' => $row['explanation'] ?? null,
                ]);

                $count++;
            }

            return $count;
        });

        $this->reset('importFile');

        $this->dispatch('toast', message: "Imported {$imported} question(s).");
    }

    public function startDeleteConfirm(): void
    {
        $this->confirmingDelete = true;
        $this->deleteConfirmText = '';
    }

    public function cancelDeleteConfirm(): void
    {
        $this->reset(['confirmingDelete', 'deleteConfirmText']);
    }

    public function confirmDelete(): void
    {
        if ($this->deleteConfirmText !== 'DELETE') {
            $this->addError('deleteConfirmText', 'Type DELETE exactly to confirm.');

            return;
        }

        ResetQuestionPool::dispatch();

        $this->reset(['confirmingDelete', 'deleteConfirmText']);

        $this->dispatch('toast', message: 'Question pool deleted.');
    }

    public function startClearConfirm(): void
    {
        $this->confirmingClear = true;
        $this->clearConfirmText = '';
    }

    public function cancelClearConfirm(): void
    {
        $this->reset(['confirmingClear', 'clearConfirmText']);
    }

    public function confirmClear(): void
    {
        if ($this->clearConfirmText !== 'RESET') {
            $this->addError('clearConfirmText', 'Type RESET exactly to confirm.');

            return;
        }

        ResetMasteryProgress::dispatch();

        $this->reset(['confirmingClear', 'clearConfirmText']);

        $this->dispatch('toast', message: 'Progress cleared.');
    }

    public function render()
    {
        $totalAttempts = Attempt::count();
        $correctAttempts = Attempt::where('correct', true)->count();

        return view('livewire.settings.settings-page', [
            'questionCount' => Question::count(),
            'categoryCount' => Category::count(),
            'sessionCount' => QuizSession::count(),
            'attemptCount' => $totalAttempts,
            'avgRecall' => $totalAttempts > 0 ? (int) round($correctAttempts / $totalAttempts * 100) : 0,
        ]);
    }
}
