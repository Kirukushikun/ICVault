<?php

namespace App\Livewire\Quiz;

use App\Enums\QuestionType;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\QuizSession as QuizSessionModel;
use App\Services\MasteryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Quiz Session'])]
class QuizSession extends Component
{
    public string $screen = 'setup';

    public ?string $mode = null;

    /** @var array<int, array<string, mixed>> */
    public array $pool = [];

    public int $index = 0;

    public bool $revealed = false;

    public ?int $selectedOption = null;

    public string $fillValue = '';

    public string $codeValue = '';

    public bool $lastCorrect = false;

    public int $quizSessionId;

    private const array MODE_TYPE = [
        'mc' => QuestionType::MultipleChoice,
        'fill' => QuestionType::FillBlank,
        'code' => QuestionType::Code,
    ];

    private const array MODE_META = [
        'shuffle' => ['icon' => '⇄', 'name' => 'Shuffle', 'desc' => 'Mix of all question types — keeps your recall sharp and unpredictable.', 'kindLabel' => 'All types'],
        'mc' => ['icon' => '◉', 'name' => 'Multiple Choice', 'desc' => 'Four options, one answer. Good for drilling recognition over recall.', 'kindLabel' => 'Pick one'],
        'fill' => ['icon' => '▭', 'name' => 'Fill in the Blank', 'desc' => 'Complete the sentence. Forces you to pull the exact word from memory.', 'kindLabel' => 'Type answer'],
        'code' => ['icon' => '</>', 'name' => 'Write the Code', 'desc' => 'Open-ended. No hints, no options — write it from scratch.', 'kindLabel' => 'Open-ended'],
    ];

    public function mount(): void
    {
        $this->quizSessionId = QuizSessionModel::today()->id;
    }

    /** @return array<int, array<string, mixed>> */
    public function modeOptions(): array
    {
        $counts = [
            'shuffle' => Question::count(),
            'mc' => Question::where('type', QuestionType::MultipleChoice)->count(),
            'fill' => Question::where('type', QuestionType::FillBlank)->count(),
            'code' => Question::where('type', QuestionType::Code)->count(),
        ];

        return collect(self::MODE_META)->map(fn (array $meta, string $mode) => [
            'mode' => $mode,
            ...$meta,
            'countLabel' => $counts[$mode].' question'.($counts[$mode] === 1 ? '' : 's'),
        ])->values()->all();
    }

    public function currentQuestion(): ?array
    {
        return $this->pool[$this->index % max(count($this->pool), 1)] ?? null;
    }

    public function selectMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function start(): void
    {
        if (! $this->mode) {
            return;
        }

        $query = Question::with('category');

        if ($this->mode !== 'shuffle') {
            $query->where('type', self::MODE_TYPE[$this->mode]);
        }

        $this->pool = $query->inRandomOrder()->get()->map(fn (Question $q) => [
            'id' => $q->id,
            'type' => $q->type->value,
            'diff' => $q->difficulty->value,
            'diffLabel' => ucfirst($q->difficulty->value).' · '.match ($q->type) {
                QuestionType::MultipleChoice => 'Multiple Choice',
                QuestionType::FillBlank => 'Fill in the Blank',
                QuestionType::Code => 'Write the Code',
            },
            'tag' => $q->category->name,
            'question' => $q->prompt,
            'options' => $q->options_json,
            'answer' => $q->answer,
            'explanation' => $q->explanation,
        ])->all();

        $this->index = 0;
        $this->resetAnswerState();
        $this->screen = 'active';
    }

    public function exit(): void
    {
        $this->screen = 'setup';
        $this->mode = null;
        $this->pool = [];
        $this->index = 0;
        $this->resetAnswerState();
    }

    public function selectOption(int $i): void
    {
        if (! $this->revealed) {
            $this->selectedOption = $i;
        }
    }

    public function submit(): void
    {
        $current = $this->currentQuestion();

        if ($this->revealed || ! $current) {
            return;
        }

        if (in_array($current['type'], ['multiple_choice', 'fill_blank'], true)) {
            $correct = $current['type'] === 'multiple_choice'
                ? $this->selectedOption !== null && ($current['options'][$this->selectedOption] ?? null) === $current['answer']
                : trim(mb_strtolower($this->fillValue)) === trim(mb_strtolower($current['answer']));

            $this->lastCorrect = $correct;

            $question = Question::find($current['id']);
            Attempt::create([
                'question_id' => $question->id,
                'quiz_session_id' => $this->quizSessionId,
                'correct' => $correct,
                'answered_at' => now(),
            ]);
            app(MasteryService::class)->recordAttempt($question, $correct);
        }

        // Code questions are self-graded (reference-answer reveal only) — no
        // Attempt/mastery change, matching the mockup's neutral "◆ Reference Answer".
        QuizSessionModel::find($this->quizSessionId)?->increment('completed_count');

        $this->revealed = true;
    }

    public function next(): void
    {
        $this->index = count($this->pool) ? ($this->index + 1) % count($this->pool) : 0;
        $this->resetAnswerState();
    }

    public function skip(): void
    {
        $this->next();
    }

    private function resetAnswerState(): void
    {
        $this->revealed = false;
        $this->selectedOption = null;
        $this->fillValue = '';
        $this->codeValue = '';
        $this->lastCorrect = false;
    }

    public function render()
    {
        return view('livewire.quiz.quiz-session', [
            'modeOptions' => $this->modeOptions(),
            'current' => $this->currentQuestion(),
            'modeLabel' => $this->mode ? self::MODE_META[$this->mode]['name'] : '',
        ]);
    }
}
