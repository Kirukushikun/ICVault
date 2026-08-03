<?php

namespace App\Tools\Quiz\Livewire;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Enums\QuestionType;
use App\Tools\Quiz\Jobs\ParseImportBatch;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use App\Tools\Quiz\Services\ImportPipelineService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['title' => 'ICVault — Import & Logs'])]
class ImportPage extends Component
{
    use WithFileUploads;

    /** Structured Markdown leads: it's the one path that always works, with no key and no API cost. */
    public string $tab = 'markdown';

    public $noteFile = null;

    public string $sessionLogText = '';

    public $markdownFile = null;

    public string $markdownText = '';

    public ?int $categoryId = null;

    #[Url(as: 'batch')]
    public ?int $lastBatchId = null;

    public bool $aiEnabled = true;

    /**
     * Every parsed-but-unsaved candidate, editable. Only ever populated by
     * `generate()` (fresh parse) or `mount()` (resuming a `Ready` batch via
     * the `?batch=` param) — never by `render()`, which runs on every
     * round trip including ones where an edit or a discard already changed
     * this in memory but nothing has been written back to the batch yet.
     *
     * `answerIndex` points at whichever entry of `options` is correct, so a
     * multiple-choice answer can't drift out of sync with its own options
     * when the reader edits the option's text — for the other two types the
     * free-text `answer` is the real one and `answerIndex` stays null.
     *
     * `isAi` and `excerpt` exist purely for the review screen: they tell the
     * reader whether a row was read verbatim out of their own file or drafted
     * by a model, and in the latter case what it was drafted from. Neither
     * survives the save — a Question is a Question however it got written.
     *
     * @var array<int, array{type: string, difficulty: string, prompt: string, answer: string, options: array<int, string>, answerIndex: ?int, explanation: string, isAi: bool, excerpt: ?string}>
     */
    public array $candidates = [];

    public function mount(): void
    {
        $this->categoryId = Category::query()->orderBy('name')->value('id');
        $this->aiEnabled = (bool) config('services.anthropic.enabled');

        if (! $this->aiEnabled && in_array($this->tab, ['notes', 'session'], true)) {
            $this->tab = 'markdown';
        }

        if ($this->lastBatchId) {
            $batch = ImportBatch::find($this->lastBatchId);

            if ($batch && $batch->status === ImportStatus::Ready) {
                $this->populateCandidatesFrom($batch);
            }
        }
    }

    public function generate(): void
    {
        if (in_array($this->tab, ['notes', 'session'], true) && ! $this->aiEnabled) {
            $this->addError('tab', 'AI question generation is turned off.');

            return;
        }

        $this->validate(['categoryId' => ['required', 'exists:categories,id']]);

        if ($this->tab === 'session') {
            $this->validate(['sessionLogText' => ['required', 'string']]);
            $rawContent = $this->sessionLogText;
            $sourceType = 'log';
        } elseif ($this->tab === 'notes') {
            // File only: a pasted note is just a long Quick Capture, and
            // giving it two doors into the same behaviour would only blur
            // what each tab is for.
            $this->validate(['noteFile' => ['required', 'file', 'extensions:md', 'max:2048']]);
            $rawContent = $this->noteFile->get();
            $this->noteFile->storeAs('imports', $this->noteFile->getClientOriginalName());
            $sourceType = 'note';
        } else {
            $rawContent = $this->readMarkdownUploadOrText();
            $sourceType = 'markdown';

            if ($rawContent === null) {
                return;
            }
        }

        // An upload that reads as nothing would otherwise surface as a
        // baffling parser complaint about a file the reader can see is fine.
        if (trim((string) $rawContent) === '') {
            $this->addError($this->tab === 'notes' ? 'noteFile' : 'markdownFile',
                "That file came through empty. Re-select it and try again.");

            return;
        }

        $batch = ImportBatch::create([
            'source_type' => $sourceType,
            'category_id' => $this->categoryId,
            'raw_content' => $rawContent,
            'status' => ImportStatus::Uploaded,
        ]);

        // No queue worker runs in this environment, and the manual-Markdown
        // path has no I/O reason to be async either — this blocks the
        // request instead of leaving the batch stuck at `uploaded` forever.
        ParseImportBatch::dispatchSync($batch->id);

        $this->lastBatchId = $batch->id;
        $this->reset(['noteFile', 'sessionLogText', 'markdownFile', 'markdownText']);
        $this->candidates = [];

        $batch->refresh();

        if ($batch->status === ImportStatus::Ready) {
            $this->populateCandidatesFrom($batch);
            $this->dispatch('toast', message: count($this->candidates).' question(s) ready to review below');
        } else {
            $this->dispatch('toast', message: '⚠ Import failed — see the reason on the right');
        }
    }

    /**
     * Resolves the Ready-made tab's content from exactly one of its two
     * inputs, or returns null having flagged the problem. Requiring one or
     * the other — rather than quietly preferring the file — means an
     * abandoned upload can never silently win over text just typed.
     */
    private function readMarkdownUploadOrText(): ?string
    {
        $hasFile = (bool) $this->markdownFile;
        $hasText = trim($this->markdownText) !== '';

        if ($hasFile === $hasText) {
            $this->addError('markdownFile', $hasFile
                ? 'Provide either a file or pasted text, not both.'
                : 'Upload a .md file or paste Markdown text.');

            return null;
        }

        if (! $hasFile) {
            return $this->markdownText;
        }

        $this->validate(['markdownFile' => ['file', 'extensions:md', 'max:2048']]);

        // Read before storing: `storeAs()` moves the Livewire temp file, so
        // reading afterwards hits a path that no longer exists.
        $content = $this->markdownFile->get();
        $this->markdownFile->storeAs('imports', $this->markdownFile->getClientOriginalName());

        return $content;
    }

    public function discardCandidate(int $index): void
    {
        unset($this->candidates[$index]);
        $this->candidates = array_values($this->candidates);
    }

    /** Marks which option is the correct one, by position rather than by text. */
    public function markCorrect(int $index, int $optionIndex): void
    {
        $this->candidates[$index]['answerIndex'] = $optionIndex;
    }

    public function addOption(int $index): void
    {
        $this->candidates[$index]['options'][] = '';
    }

    public function removeOption(int $index, int $optionIndex): void
    {
        $candidate = $this->candidates[$index];
        unset($candidate['options'][$optionIndex]);
        $candidate['options'] = array_values($candidate['options']);

        // The correct answer is a position, so removing an option above it
        // shifts it — and removing the correct one itself clears the mark
        // rather than silently promoting its neighbour.
        $candidate['answerIndex'] = match (true) {
            $candidate['answerIndex'] === $optionIndex => null,
            $candidate['answerIndex'] > $optionIndex => $candidate['answerIndex'] - 1,
            default => $candidate['answerIndex'],
        };

        $this->candidates[$index] = $candidate;
    }

    /** Commits a single reviewed row, leaving the rest to be dealt with separately. */
    public function saveCandidate(int $index): void
    {
        $this->resetErrorBag();

        $payload = $this->payloadFor($index);

        if ($payload === null) {
            return;
        }

        $batch = ImportBatch::findOrFail($this->lastBatchId);
        app(ImportPipelineService::class)->commitQuestions($batch, [$payload]);

        $this->discardCandidate($index);
        $this->closeBatchIfReviewed($batch);

        $this->dispatch('toast', message: '✓ Saved 1 question to the pool');
    }

    /**
     * Commits every remaining candidate, all-or-nothing: a partial save on a
     * bulk action would leave the reader guessing which rows made it, and
     * anything they want to handle separately can go through
     * `saveCandidate()` instead.
     */
    public function saveCandidates(): void
    {
        if ($this->candidates === []) {
            return;
        }

        $this->resetErrorBag();

        $payload = [];
        $failed = false;

        foreach (array_keys($this->candidates) as $i) {
            $row = $this->payloadFor($i);

            $failed = $failed || $row === null;
            $payload[] = $row;
        }

        if ($failed) {
            return;
        }

        $batch = ImportBatch::findOrFail($this->lastBatchId);
        app(ImportPipelineService::class)->commitQuestions($batch, $payload);

        $count = count($payload);
        $this->candidates = [];
        $this->closeBatchIfReviewed($batch);

        $this->dispatch('toast', message: "✓ Saved {$count} question(s) to the pool");
    }

    /**
     * Validates one candidate into the shape `Question::create()` wants, or
     * returns null having flagged what's wrong on the row itself — the
     * review screen shows errors inline, so a failure has to name the exact
     * field it belongs to.
     */
    private function payloadFor(int $index): ?array
    {
        $c = $this->candidates[$index];
        $ok = true;

        if (trim($c['prompt']) === '') {
            $this->addError("candidates.{$index}.prompt", 'A question needs a prompt.');
            $ok = false;
        }

        if (! in_array($c['difficulty'], array_column(Difficulty::cases(), 'value'), true)
            || ! in_array($c['type'], array_column(QuestionType::cases(), 'value'), true)) {
            $this->addError("candidates.{$index}.type", 'Unrecognised question type or difficulty.');
            $ok = false;
        }

        $options = null;
        $answer = trim($c['answer']);

        if ($c['type'] === QuestionType::MultipleChoice->value) {
            // Blank rows are just unfilled UI, not real options — but the
            // correct answer is tracked by position, so it has to be read
            // before the blanks collapse the indexes.
            $answer = trim($c['options'][$c['answerIndex']] ?? '');
            $options = array_values(array_filter(array_map('trim', $c['options'])));

            if (count($options) < 2) {
                $this->addError("candidates.{$index}.options", 'Give this question at least two options.');
                $ok = false;
            }

            if ($answer === '') {
                $this->addError("candidates.{$index}.options", 'Mark one option as the correct answer.');
                $ok = false;
            }
        } elseif ($answer === '') {
            $this->addError("candidates.{$index}.answer", 'A question needs an answer.');
            $ok = false;
        }

        if (! $ok) {
            return null;
        }

        return [
            'difficulty' => $c['difficulty'],
            'type' => $c['type'],
            'prompt' => trim($c['prompt']),
            'options_json' => $options,
            'answer' => $answer,
            'explanation' => trim($c['explanation']) !== '' ? trim($c['explanation']) : null,
        ];
    }

    /** A batch is only done once nothing is left staged on the review screen. */
    private function closeBatchIfReviewed(ImportBatch $batch): void
    {
        if ($this->candidates !== []) {
            return;
        }

        app(ImportPipelineService::class)->transitionTo($batch, ImportStatus::Imported);
    }

    /** Splits each candidate's options into individually editable rows, with the correct one held by position. */
    private function populateCandidatesFrom(ImportBatch $batch): void
    {
        $isAi = $batch->source_type !== 'markdown';

        $this->candidates = collect($batch->candidates_json ?? [])
            ->map(function (array $c) use ($isAi) {
                $options = $c['options_json'] ?: [];
                $answerIndex = array_search($c['answer'], $options, true);

                return [
                    'type' => $c['type'],
                    'difficulty' => $c['difficulty'],
                    'prompt' => $c['prompt'],
                    'answer' => $c['answer'],
                    // Two blank rows so switching a candidate to multiple
                    // choice lands on a usable form rather than an empty one.
                    'options' => $options ?: ['', ''],
                    'answerIndex' => $answerIndex === false ? null : $answerIndex,
                    'explanation' => $c['explanation'] ?? '',
                    'isAi' => $isAi,
                    'excerpt' => $isAi ? ($c['source_excerpt'] ?? null) : null,
                ];
            })
            ->all();
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
