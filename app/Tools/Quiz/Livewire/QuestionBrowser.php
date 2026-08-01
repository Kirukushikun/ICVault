<?php

namespace App\Tools\Quiz\Livewire;

use App\Tools\Quiz\Enums\Difficulty;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'ICVault — Library'])]
class QuestionBrowser extends Component
{
    public string $search = '';

    public string $difficulty = 'all';

    public string $categoryId = 'all';

    public ?int $editingId = null;

    public string $editPrompt = '';

    public string $editAnswer = '';

    public string $editExplanation = '';

    public string $editDifficulty = '';

    public ?int $editCategoryId = null;

    public string $editOptions = '';

    public function editQuestion(int $id): void
    {
        $question = Question::findOrFail($id);

        $this->editingId = $question->id;
        $this->editPrompt = $question->prompt;
        $this->editAnswer = $question->answer;
        $this->editExplanation = (string) $question->explanation;
        $this->editDifficulty = $question->difficulty->value;
        $this->editCategoryId = $question->category_id;
        $this->editOptions = $question->options_json ? implode("\n", $question->options_json) : '';
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editPrompt', 'editAnswer', 'editExplanation', 'editDifficulty', 'editCategoryId', 'editOptions']);
    }

    public function updateQuestion(): void
    {
        $question = Question::findOrFail($this->editingId);

        $validated = $this->validate([
            'editPrompt' => ['required', 'string'],
            'editAnswer' => ['required', 'string'],
            'editExplanation' => ['nullable', 'string'],
            'editDifficulty' => ['required', 'in:easy,medium,hard'],
            'editCategoryId' => ['required', 'exists:categories,id'],
        ]);

        $options = null;
        if ($question->type->value === 'multiple_choice') {
            $options = collect(explode("\n", $this->editOptions))
                ->map(fn (string $line) => trim($line))
                ->filter()
                ->values()
                ->all();
        }

        $question->update([
            'prompt' => $validated['editPrompt'],
            'answer' => $validated['editAnswer'],
            'explanation' => $validated['editExplanation'] ?: null,
            'difficulty' => $validated['editDifficulty'],
            'category_id' => $validated['editCategoryId'],
            'options_json' => $options,
        ]);

        $this->cancelEdit();

        $this->dispatch('toast', message: 'Question updated.');
    }

    public function deleteQuestion(int $id): void
    {
        Question::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        $this->dispatch('toast', message: 'Question deleted.');
    }

    public function render()
    {
        $base = Question::query()
            ->when($this->search !== '', fn ($query) => $query->where('prompt', 'like', '%'.$this->search.'%'))
            ->when($this->categoryId !== 'all', fn ($query) => $query->where('category_id', $this->categoryId));

        $counts = (clone $base)
            ->selectRaw('difficulty, count(*) as aggregate')
            ->groupBy('difficulty')
            ->pluck('aggregate', 'difficulty');

        $questions = (clone $base)
            ->when($this->difficulty !== 'all', fn ($query) => $query->where('difficulty', $this->difficulty))
            ->with('category')
            ->latest()
            ->get();

        return view('tools.quiz.library', [
            'questions' => $questions,
            'categories' => Category::orderBy('name')->get(),
            'difficulties' => Difficulty::cases(),
            'tabCounts' => [
                'all' => $counts->sum(),
                'easy' => $counts->get('easy', 0),
                'medium' => $counts->get('medium', 0),
                'hard' => $counts->get('hard', 0),
            ],
        ]);
    }
}
