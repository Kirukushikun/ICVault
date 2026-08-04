<?php

namespace App\Tools\Quiz\Livewire;

use App\Tools\Quiz\Jobs\ResetMasteryProgress;
use App\Tools\Quiz\Jobs\ResetQuestionPool;
use App\Tools\Quiz\Models\Attempt;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Tools\Quiz\Models\QuizPreference;
use App\Tools\Quiz\Models\QuizSession;
use App\Tools\Quiz\Services\PoolExportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Settings that belong to the Quiz tool: pool export/import, drill preferences
 * and the destructive resets. Account-level settings live on the platform's
 * own settings page.
 */
#[Layout('layouts.app', ['title' => 'ICVault — Quiz Settings'])]
class QuizSettings extends Component
{
    use WithFileUploads;

    public $importFile;

    public string $importMode = 'merge';

    public bool $confirmingDelete = false;

    public string $deleteConfirmText = '';

    public bool $confirmingClear = false;

    public string $clearConfirmText = '';

    public string $newCategoryName = '';

    public string $newCategoryColor = '#c3073f';

    /**
     * The `pref` prefix keeps these out of Livewire's automatic
     * `updated{Property}` hook naming — they're all persisted through the
     * single generic `updated()` hook below instead, so adding a new
     * preference never needs a matching per-property method.
     */
    public string $prefDefaultMode = 'shuffle';

    public int $prefDailyQuota = 8;

    public bool $prefAutoReveal = true;

    public bool $prefShuffleOrder = true;

    public bool $prefShowDifficulty = true;

    public bool $prefTimedMode = false;

    public bool $prefStreakReminder = true;

    public bool $prefWeeklySummary = true;

    public bool $prefNewQuestionsNotif = false;

    public function mount(): void
    {
        $pref = QuizPreference::current();

        $this->prefDefaultMode = $pref->default_mode;
        $this->prefDailyQuota = $pref->daily_quota;
        $this->prefAutoReveal = $pref->auto_reveal;
        $this->prefShuffleOrder = $pref->shuffle_order;
        $this->prefShowDifficulty = $pref->show_difficulty;
        $this->prefTimedMode = $pref->timed_mode;
        $this->prefStreakReminder = $pref->streak_reminder;
        $this->prefWeeklySummary = $pref->weekly_summary;
        $this->prefNewQuestionsNotif = $pref->new_questions_notif;
    }

    /** Persists any `pref*` property the moment it changes — no separate "Save" step. */
    public function updated(string $property): void
    {
        if (! str_starts_with($property, 'pref')) {
            return;
        }

        $column = Str::snake(substr($property, 4));

        QuizPreference::current()->update([$column => $this->{$property}]);

        $this->dispatch('toast', message: '✓ Preference saved');
    }

    public function addCategory(): void
    {
        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:60', 'unique:categories,name'],
            'newCategoryColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        Category::create([
            'name' => $this->newCategoryName,
            'slug' => Str::slug($this->newCategoryName),
            'color' => $this->newCategoryColor,
        ]);

        $this->reset(['newCategoryName', 'newCategoryColor']);
        $this->newCategoryColor = '#c3073f';

        $this->dispatch('toast', message: '✓ Category added');
    }

    public function deleteCategory(int $id): void
    {
        // Cascade-deletes the category's questions too (see the questions
        // migration's onDelete) — the confirm text on the button already
        // names how many, so this is the reader's informed call.
        Category::findOrFail($id)->delete();

        $this->dispatch('toast', message: 'Category deleted.');
    }

    public function exportJson()
    {
        $payload = app(PoolExportService::class)->toJson();

        return response()->streamDownload(
            fn () => print($payload),
            'icvault-export-'.now()->format('Y-m-d_His').'.json',
            ['Content-Type' => 'application/json']
        );
    }

    public function exportCsv()
    {
        $payload = app(PoolExportService::class)->toCsv();

        return response()->streamDownload(
            fn () => print($payload),
            'icvault-export-'.now()->format('Y-m-d_His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    public function exportMarkdown()
    {
        $payload = app(PoolExportService::class)->toMarkdown();

        return response()->streamDownload(
            fn () => print($payload),
            'icvault-export-'.now()->format('Y-m-d_His').'.md',
            ['Content-Type' => 'text/markdown']
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

        // No queue worker runs in this environment — a plain dispatch() would
        // land the job in the `jobs` table and sit there forever, making the
        // button look broken. Sync it, same reasoning as ParseImportBatch.
        ResetQuestionPool::dispatchSync();

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

        ResetMasteryProgress::dispatchSync();

        $this->reset(['confirmingClear', 'clearConfirmText']);

        $this->dispatch('toast', message: 'Progress cleared.');
    }

    /**
     * Breaks the running streak by moving its cutoff to now — QuizDashboard's
     * streak calc ignores any session on or before `streak_broken_at`.
     */
    public function resetStreak(): void
    {
        QuizPreference::current()->update(['streak_broken_at' => now()]);

        $this->dispatch('toast', message: 'Streak reset.');
    }

    public function render()
    {
        $totalAttempts = Attempt::count();
        $correctAttempts = Attempt::where('correct', true)->count();

        return view('tools.quiz.settings', [
            'questionCount' => Question::count(),
            'categoryCount' => Category::count(),
            'sessionCount' => QuizSession::count(),
            'attemptCount' => $totalAttempts,
            'avgRecall' => $totalAttempts > 0 ? (int) round($correctAttempts / $totalAttempts * 100) : 0,
            'categories' => Category::withCount('questions')->orderBy('name')->get(),
        ]);
    }
}
