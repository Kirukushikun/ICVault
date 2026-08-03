<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Livewire\QuestionBrowser;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionBrowserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_lists_questions_with_difficulty_tab_counts(): void
    {
        Question::factory()->fillBlank('a')->create(['difficulty' => 'easy']);
        Question::factory()->fillBlank('b')->create(['difficulty' => 'medium']);
        Question::factory()->fillBlank('c')->create(['difficulty' => 'medium']);

        Livewire::test(QuestionBrowser::class)
            ->assertViewHas('tabCounts', fn (array $counts) => $counts === ['all' => 3, 'easy' => 1, 'medium' => 2, 'hard' => 0])
            ->assertViewHas('questions', fn ($questions) => $questions->count() === 3);
    }

    public function test_it_filters_by_search_term(): void
    {
        Question::factory()->fillBlank('a')->create(['prompt' => 'What does dispatch() do?']);
        Question::factory()->fillBlank('b')->create(['prompt' => 'Explain git rebase']);

        Livewire::test(QuestionBrowser::class)
            ->set('search', 'rebase')
            ->assertViewHas('questions', fn ($questions) => $questions->count() === 1 && $questions->first()->prompt === 'Explain git rebase');
    }

    public function test_it_filters_by_difficulty_tab(): void
    {
        Question::factory()->fillBlank('a')->create(['difficulty' => 'easy']);
        Question::factory()->fillBlank('b')->create(['difficulty' => 'hard']);

        Livewire::test(QuestionBrowser::class)
            ->set('difficulty', 'hard')
            ->assertViewHas('questions', fn ($questions) => $questions->count() === 1 && $questions->first()->difficulty->value === 'hard');
    }

    public function test_it_filters_by_category(): void
    {
        $matching = Category::factory()->create();
        $other = Category::factory()->create();

        Question::factory()->fillBlank('a')->for($matching)->create();
        Question::factory()->fillBlank('b')->for($other)->create();

        Livewire::test(QuestionBrowser::class)
            ->set('categoryId', (string) $matching->id)
            ->assertViewHas('questions', fn ($questions) => $questions->count() === 1);
    }

    public function test_it_updates_a_question(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->fillBlank('old answer')->create();

        Livewire::test(QuestionBrowser::class)
            ->call('editQuestion', $question->id)
            ->set('editPrompt', 'Updated prompt')
            ->set('editAnswer', 'new answer')
            ->set('editExplanation', 'because reasons')
            ->set('editDifficulty', 'hard')
            ->set('editCategoryId', $category->id)
            ->call('updateQuestion')
            ->assertSet('editingId', null);

        $question->refresh();
        $this->assertSame('Updated prompt', $question->prompt);
        $this->assertSame('new answer', $question->answer);
        $this->assertSame('because reasons', $question->explanation);
        $this->assertSame('hard', $question->difficulty->value);
        $this->assertSame($category->id, $question->category_id);
    }

    /**
     * The whole point of holding the answer by position: editing the marked
     * option's text carries the answer with it, where the old
     * newline-textarea plus free-text answer let the two drift apart.
     */
    public function test_a_multiple_choice_answer_follows_the_marked_option_when_edited(): void
    {
        $question = Question::factory()->create([
            'type' => 'multiple_choice',
            'options_json' => ['first', 'second', 'third'],
            'answer' => 'second',
        ]);

        Livewire::test(QuestionBrowser::class)
            ->call('editQuestion', $question->id)
            ->assertSet('editAnswerIndex', 1)
            ->set('editOptions.1', 'second, corrected')
            ->call('updateQuestion')
            ->assertHasNoErrors();

        $question->refresh();
        $this->assertSame('second, corrected', $question->answer);
        $this->assertSame(['first', 'second, corrected', 'third'], $question->options_json);
    }

    public function test_it_rejects_a_multiple_choice_question_with_no_option_marked_correct(): void
    {
        $question = Question::factory()->create([
            'type' => 'multiple_choice',
            'options_json' => ['first', 'second'],
            'answer' => 'first',
        ]);

        Livewire::test(QuestionBrowser::class)
            ->call('editQuestion', $question->id)
            ->call('removeOption', 0)
            ->call('updateQuestion')
            ->assertHasErrors(['editOptions']);

        $this->assertSame('first', $question->fresh()->answer);
    }

    public function test_removing_an_option_shifts_the_correct_mark(): void
    {
        $question = Question::factory()->create([
            'type' => 'multiple_choice',
            'options_json' => ['a', 'b', 'c'],
            'answer' => 'c',
        ]);

        Livewire::test(QuestionBrowser::class)
            ->call('editQuestion', $question->id)
            ->assertSet('editAnswerIndex', 2)
            ->call('removeOption', 0)
            ->assertSet('editAnswerIndex', 1)
            ->call('updateQuestion');

        $this->assertSame('c', $question->fresh()->answer);
    }

    public function test_it_requires_a_prompt_and_answer_to_update(): void
    {
        $question = Question::factory()->fillBlank('old answer')->create();

        Livewire::test(QuestionBrowser::class)
            ->call('editQuestion', $question->id)
            ->set('editPrompt', '')
            ->set('editAnswer', '')
            ->call('updateQuestion')
            ->assertHasErrors(['editPrompt', 'editAnswer']);
    }

    public function test_it_deletes_a_question(): void
    {
        $question = Question::factory()->fillBlank('a')->create();

        Livewire::test(QuestionBrowser::class)
            ->call('deleteQuestion', $question->id);

        $this->assertModelMissing($question);
    }
}
