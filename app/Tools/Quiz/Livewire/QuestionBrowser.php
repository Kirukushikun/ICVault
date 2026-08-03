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

    /** @var array<int, string> */
    public array $editOptions = [];

    /**
     * Which entry of `editOptions` is correct. Holding the answer by position
     * rather than matching its text means editing an option can't quietly
     * orphan the answer — the same guarantee the import review screen makes.
     */
    public ?int $editAnswerIndex = null;

    public function editQuestion(int $id): void
    {
        $question = Question::findOrFail($id);
        $options = $question->options_json ?: [];
        $answerIndex = array_search($question->answer, $options, true);

        $this->editingId = $question->id;
        $this->editPrompt = $question->prompt;
        $this->editAnswer = $question->answer;
        $this->editExplanation = (string) $question->explanation;
        $this->editDifficulty = $question->difficulty->value;
        $this->editCategoryId = $question->category_id;
        $this->editOptions = $options ?: ['', ''];
        $this->editAnswerIndex = $answerIndex === false ? null : $answerIndex;
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'editingId', 'editPrompt', 'editAnswer', 'editExplanation',
            'editDifficulty', 'editCategoryId', 'editOptions', 'editAnswerIndex',
        ]);
    }

    public function markCorrect(int $optionIndex): void
    {
        $this->editAnswerIndex = $optionIndex;
    }

    public function addOption(): void
    {
        $this->editOptions[] = '';
    }

    public function removeOption(int $optionIndex): void
    {
        unset($this->editOptions[$optionIndex]);
        $this->editOptions = array_values($this->editOptions);

        // Removing an option above the correct one shifts it; removing the
        // correct one itself clears the mark rather than promoting a neighbour.
        $this->editAnswerIndex = match (true) {
            $this->editAnswerIndex === $optionIndex => null,
            $this->editAnswerIndex > $optionIndex => $this->editAnswerIndex - 1,
            default => $this->editAnswerIndex,
        };
    }

    public function updateQuestion(): void
    {
        $question = Question::findOrFail($this->editingId);
        $isMultipleChoice = $question->type->value === 'multiple_choice';

        $validated = $this->validate([
            'editPrompt' => ['required', 'string'],
            // Multiple choice takes its answer from the marked option, so the
            // free-text field isn't in play and mustn't be required.
            'editAnswer' => [$isMultipleChoice ? 'nullable' : 'required', 'string'],
            'editExplanation' => ['nullable', 'string'],
            'editDifficulty' => ['required', 'in:easy,medium,hard'],
            'editCategoryId' => ['required', 'exists:categories,id'],
        ]);

        $options = null;
        $answer = trim((string) $validated['editAnswer']);

        if ($isMultipleChoice) {
            // Read the answer before blank rows collapse the indexes.
            $answer = trim($this->editOptions[$this->editAnswerIndex] ?? '');
            $options = array_values(array_filter(array_map('trim', $this->editOptions)));

            if (count($options) < 2) {
                $this->addError('editOptions', 'Give this question at least two options.');

                return;
            }

            if ($answer === '') {
                $this->addError('editOptions', 'Mark one option as the correct answer.');

                return;
            }
        }

        $question->update([
            'prompt' => $validated['editPrompt'],
            'answer' => $answer,
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
