<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds. Mirrors the mockup's 6 sample quiz questions
     * (project-overview/index.html's `allQuestions` array) so Quiz Session
     * has real data to draw from.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $questions = [
            [
                'category_id' => $categories['laravel'],
                'difficulty' => Difficulty::Easy,
                'type' => QuestionType::MultipleChoice,
                'prompt' => 'Which artisan command creates a new database migration?',
                'options_json' => ['php artisan make:model', 'php artisan make:migration', 'php artisan migrate:fresh', 'php artisan db:seed'],
                'answer' => 'php artisan make:migration',
                'explanation' => '<code>make:migration</code> generates a new migration file inside <code>database/migrations/</code>.',
            ],
            [
                'category_id' => $categories['git'],
                'difficulty' => Difficulty::Medium,
                'type' => QuestionType::MultipleChoice,
                'prompt' => 'Which git command rewrites commit history?',
                'options_json' => ['git merge', 'git fetch', 'git rebase', 'git cherry-pick'],
                'answer' => 'git rebase',
                'explanation' => '<code>git rebase</code> replays commits on top of another branch, rewriting their hashes. <code>git merge</code> preserves the original history.',
            ],
            [
                'category_id' => $categories['vue-blade'],
                'difficulty' => Difficulty::Medium,
                'type' => QuestionType::FillBlank,
                'prompt' => '<code>x-slot</code> goes ____ the component tag, not outside.',
                'options_json' => null,
                'answer' => 'inside',
                'explanation' => '<code>x-slot</code> must go <strong>inside</strong> the component tag so Blade knows which slot to populate.',
            ],
            [
                'category_id' => $categories['laravel'],
                'difficulty' => Difficulty::Easy,
                'type' => QuestionType::FillBlank,
                'prompt' => '<code>dispatchSync()</code> runs a job ____ and waits for it to finish.',
                'options_json' => null,
                'answer' => 'inline',
                'explanation' => '<code>dispatchSync()</code> runs the job <strong>inline</strong> (synchronously) and blocks until it completes, unlike <code>dispatch()</code> which queues it.',
            ],
            [
                'category_id' => $categories['vue-blade'],
                'difficulty' => Difficulty::Hard,
                'type' => QuestionType::Code,
                'prompt' => 'Write a Blade directive that loops over $items and shows "No items" when the collection is empty.',
                'options_json' => null,
                'answer' => "@forelse(\$items as \$item)\n    <li>{{ \$item->name }}</li>\n@empty\n    <p>No items</p>\n@endforelse",
                'explanation' => '<code>@forelse</code> combines the loop and the empty state — no need for a separate <code>@if(count(...))</code> check.',
            ],
            [
                'category_id' => $categories['sql'],
                'difficulty' => Difficulty::Hard,
                'type' => QuestionType::Code,
                'prompt' => 'Write a SQL query to find duplicate emails in a users table.',
                'options_json' => null,
                'answer' => "SELECT email, COUNT(*) AS cnt\nFROM users\nGROUP BY email\nHAVING COUNT(*) > 1;",
                'explanation' => 'Group by the column you want to deduplicate, then filter with <code>HAVING COUNT(*) > 1</code> to keep only the duplicated rows.',
            ],
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['category_id' => $question['category_id'], 'prompt' => $question['prompt']],
                $question,
            );
        }
    }
}
