<?php

namespace App\Livewire\Settings;

use App\Models\Attempt;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizSession;
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

    public function exportJson()
    {
        $questions = Question::with('category')->get()->map(fn (Question $question) => [
            'category_slug' => $question->category?->slug,
            'category_name' => $question->category?->name,
            'category_color' => $question->category?->color,
            'difficulty' => $question->difficulty->value,
            'type' => $question->type->value,
            'prompt' => $question->prompt,
            'options' => $question->options_json,
            'answer' => $question->answer,
            'explanation' => $question->explanation,
        ]);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'questions' => $questions,
        ];

        return response()->streamDownload(
            fn () => print(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
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

    public function render()
    {
        $totalAttempts = Attempt::count();
        $correctAttempts = Attempt::where('correct', true)->count();

        return view('livewire.settings.settings-page', [
            'questionCount' => Question::count(),
            'categoryCount' => Category::count(),
            'sessionCount' => QuizSession::count(),
            'avgRecall' => $totalAttempts > 0 ? (int) round($correctAttempts / $totalAttempts * 100) : 0,
        ]);
    }
}
