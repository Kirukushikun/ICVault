<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tip;
use Illuminate\Database\Seeder;

class TipSeeder extends Seeder
{
    /**
     * Run the database seeds. First tip mirrors the mockup's hardcoded
     * Did-You-Know card; the rest give every seeded category at least one
     * tip so Dashboard has something to show regardless of which
     * category today's quiz session touches.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $tips = [
            [
                'category_id' => $categories['laravel'],
                'body' => 'In Laravel, <code>dispatch()</code> queues a job and returns immediately — <code>dispatchSync()</code> runs it inline and waits for it to finish before continuing.',
            ],
            [
                'category_id' => $categories['vue-blade'],
                'body' => '<code>x-slot</code> goes inside the component tag, not outside — Blade parses it as a named slot on the enclosing component.',
            ],
            [
                'category_id' => $categories['git'],
                'body' => '<code>git rebase</code> rewrites commit hashes as it replays them; never rebase commits that have already been pushed to a shared branch.',
            ],
            [
                'category_id' => $categories['sql'],
                'body' => 'A <code>LEFT JOIN</code> keeps every row from the left table even when there\'s no match on the right — unmatched columns come back <code>NULL</code>.',
            ],
            [
                'category_id' => $categories['js-fundamentals'],
                'body' => '<code>==</code> coerces types before comparing; <code>===</code> does not. Prefer <code>===</code> unless you specifically want the coercion.',
            ],
        ];

        foreach ($tips as $tip) {
            Tip::updateOrCreate(['category_id' => $tip['category_id'], 'body' => $tip['body']], $tip);
        }
    }
}
