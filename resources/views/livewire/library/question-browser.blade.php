<div x-data="questionBrowser()">
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Question Pool</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Library</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Drill by category, or shuffle the whole pool.
        </div>
    </div>

    <div class="flex gap-2 mb-4.5">
        <template x-for="tab in tabs" :key="tab.filter">
            <button @click="filter = tab.filter" class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                    :class="filter === tab.filter ? 'text-white border-transparent' : 'text-text-muted border-border'"
                    :style="filter === tab.filter ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''"
                    x-text="tab.label"></button>
        </template>
    </div>

    <div class="flex flex-col gap-2.5">
        <template x-for="q in filteredQuestions" :key="q.text">
            <div class="card p-[14px_16px] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full shrink-0"
                      :style="`background:${ { easy: '#4fcf95', medium: '#e0a94a', hard: '#ec5c86' }[q.diff] }`"></span>
                <span class="text-[12.5px] flex-1" x-text="q.text"></span>
                <span class="text-[9.5px] text-text-muted px-2.5 py-1 rounded-full border border-border" x-text="`${q.tag} · ${q.diffLabel}`"></span>
            </div>
        </template>
        <div x-show="filteredQuestions.length === 0" class="card p-6 text-text-muted text-sm text-center">
            No questions at this difficulty yet.
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('questionBrowser', () => ({
        filter: 'all',
        tabs: [
            { filter: 'all', label: 'All (184)' },
            { filter: 'easy', label: 'Easy (61)' },
            { filter: 'medium', label: 'Medium (74)' },
            { filter: 'hard', label: 'Hard (49)' },
        ],
        questions: [
            { text: 'Which artisan command creates a new database migration?', tag: 'Laravel', diff: 'easy', diffLabel: 'Easy' },
            { text: 'Fill in the blank: middleware defined in the ____ property run before the controller.', tag: 'Laravel', diff: 'medium', diffLabel: 'Medium' },
            { text: 'Write a Blade directive that loops over $items and shows "No items" when empty.', tag: 'Blade', diff: 'hard', diffLabel: 'Hard' },
            { text: 'git rebase vs git merge — which rewrites commit history?', tag: 'Git', diff: 'easy', diffLabel: 'Easy' },
            { text: 'Write a SQL query to find duplicate emails in a users table.', tag: 'SQL', diff: 'hard', diffLabel: 'Hard' },
        ],
        get filteredQuestions() {
            return this.filter === 'all' ? this.questions : this.questions.filter(q => q.diff === this.filter);
        },
    }));
</script>
@endscript
