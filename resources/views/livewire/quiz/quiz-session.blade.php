<div x-data="quizSession()">

    <template x-if="screen === 'setup'">
        <div>
            <div class="mb-7">
                <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Quiz Session</div>
                <div class="font-display text-[28px] font-bold tracking-wide">Choose a Mode</div>
                <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
                    Pick how you want to be tested — or go full shuffle to keep it unpredictable.
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5 mb-7">
                <template x-for="option in modeOptions" :key="option.mode">
                    <div @click="selectMode(option.mode)"
                         class="p-[24px_22px] rounded-2xl border-[1.5px] bg-card-bg cursor-pointer transition-all relative overflow-hidden"
                         :class="mode === option.mode ? 'border-red shadow-[0_8px_28px_rgba(195,7,63,0.18)] -translate-y-1' : 'border-border hover:border-red/35 hover:-translate-y-0.5'">
                        <span class="text-[22px] mb-3.5 block" x-text="option.icon"></span>
                        <div class="font-display text-base font-bold tracking-wide mb-1.5" x-text="option.name"></div>
                        <div class="text-[11.5px] text-text-muted leading-[1.55] mb-3.5" x-text="option.desc"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9.5px] font-semibold tracking-[0.12em] uppercase px-2.5 py-1 rounded-full border"
                                  :class="mode === option.mode ? 'border-red/35 text-[#ec5c86] bg-red/8' : 'border-border text-text-muted'"
                                  x-text="option.countLabel"></span>
                            <span class="text-[9.5px] font-semibold tracking-[0.12em] uppercase px-2.5 py-1 rounded-full border"
                                  :class="mode === option.mode ? 'border-red/35 text-[#ec5c86] bg-red/8' : 'border-border text-text-muted'"
                                  x-text="option.kindLabel"></span>
                            <span class="ml-auto w-5 h-5 rounded-full bg-red flex items-center justify-center text-[10px] transition-all"
                                  :class="mode === option.mode ? 'opacity-100 scale-100' : 'opacity-0 scale-50'">✓</span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end">
                <button @click="start()"
                        class="px-8 py-3.5 rounded-xl text-white text-[13px] font-bold tracking-[0.08em] uppercase transition-all"
                        :class="mode ? 'opacity-100 hover:-translate-y-0.5' : 'opacity-35 pointer-events-none'"
                        style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
                    Start Session →
                </button>
            </div>
        </div>
    </template>

    <template x-if="screen === 'active'">
        <div>
            <div class="mb-5">
                <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Session</div>
                <div class="font-display text-[28px] font-bold tracking-wide">Daily Quiz</div>
                <div class="text-[12.5px] text-text-muted mt-1.5 font-light flex items-center gap-2.5">
                    <span x-text="modeLabels[mode]"></span>
                    <button @click="exit()" class="text-[10px] text-text-muted underline tracking-[0.06em]">← Change mode</button>
                </div>
            </div>

            <div class="flex items-center gap-3.5 mb-6">
                <div class="flex-1 h-1.5 rounded-full bg-white/6 overflow-hidden">
                    <div class="h-full rounded-full transition-all" :style="`width:${progressPct}%; background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))`"></div>
                </div>
                <div class="text-[11px] text-text-muted whitespace-nowrap" x-text="`${index} / ${pool.length}`"></div>
            </div>

            <template x-if="currentQuestion">
                <div class="card p-[28px_30px] mb-4.5">
                    <span class="inline-block text-[9px] font-bold tracking-[0.14em] uppercase px-2.5 py-1 rounded-full mb-3.5"
                          :class="{
                              'bg-green/15 text-[#4fcf95]': currentQuestion.diff === 'easy',
                              'bg-amber/15 text-[#e0a94a]': currentQuestion.diff === 'medium',
                              'bg-red/15 text-[#ec5c86]': currentQuestion.diff === 'hard'
                          }"
                          x-text="currentQuestion.diffLabel"></span>
                    <div class="text-[10px] text-text-muted tracking-[0.08em] uppercase mb-2.5" x-text="currentQuestion.tag"></div>
                    <div class="text-[17px] font-medium leading-[1.5] mb-5.5 [&_code]:font-mono [&_code]:text-sm [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded"
                         x-html="currentQuestion.question"></div>

                    <template x-if="currentQuestion.type === 'mc'">
                        <div class="flex flex-col gap-2.5">
                            <template x-for="(opt, i) in currentQuestion.options" :key="i">
                                <div @click="selectOption(i)"
                                     class="flex items-center gap-3 px-4 py-3.5 rounded-[10px] border-[1.5px] bg-white/2 text-[13px] cursor-pointer transition-all"
                                     :class="{
                                         'border-red bg-red/10': !revealed && selectedOption === i,
                                         'border-border': !revealed && selectedOption !== i,
                                         'border-green bg-green/12 text-[#4fcf95]': revealed && i === currentQuestion.answer,
                                         'border-red/60 bg-red/10 text-[#ec5c86]': revealed && i !== currentQuestion.answer && i === selectedOption
                                     }">
                                    <span class="w-[22px] h-[22px] rounded-md flex items-center justify-center text-[10.5px] font-bold bg-white/6 shrink-0"
                                          x-text="String.fromCharCode(65 + i)"></span>
                                    <span x-text="opt"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="currentQuestion.type === 'fill'">
                        <input type="text" x-model="fillValue" :disabled="revealed" placeholder="Type your answer…"
                               class="w-full bg-white/3 border-[1.5px] rounded-[10px] px-4 py-3.5 text-white font-mono text-[13px] focus:outline-none"
                               :class="!revealed ? 'border-border focus:border-red' : (isCorrectFill ? 'border-green bg-green/8 text-[#4fcf95]' : 'border-red/50 bg-red/7 text-[#ec5c86]')">
                    </template>

                    <template x-if="currentQuestion.type === 'code'">
                        <div class="bg-[#0e0e10] border-[1.5px] border-border rounded-xl overflow-hidden">
                            <div class="flex items-center gap-1.5 px-3.5 py-2.5 bg-white/3 border-b border-border">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f57]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#febc2e]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#28c840]"></span>
                                <span class="ml-auto text-[10px] text-text-muted font-mono" x-text="currentQuestion.lang"></span>
                            </div>
                            <textarea x-model="codeValue" :disabled="revealed" rows="5"
                                      class="w-full bg-transparent px-4.5 py-4 font-mono text-[13px] leading-[1.7] text-white/85 focus:outline-none resize-none"></textarea>
                        </div>
                    </template>

                    <template x-if="revealed">
                        <div class="mt-4 rounded-xl border-[1.5px] border-white/7 overflow-hidden">
                            <template x-if="currentQuestion.type === 'mc'">
                                <div>
                                    <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase"
                                         :class="isCorrectMc ? 'bg-green/12 text-[#4fcf95] border-b border-green/20' : 'bg-red/10 text-[#ec5c86] border-b border-red/20'"
                                         x-text="isCorrectMc ? '✓ Correct' : '✗ Incorrect'"></div>
                                    <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded"
                                         x-html="currentQuestion.explanation"></div>
                                </div>
                            </template>
                            <template x-if="currentQuestion.type === 'fill'">
                                <div>
                                    <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase"
                                         :class="isCorrectFill ? 'bg-green/12 text-[#4fcf95] border-b border-green/20' : 'bg-red/10 text-[#ec5c86] border-b border-red/20'"
                                         x-text="isCorrectFill ? '✓ Correct' : '✗ Incorrect'"></div>
                                    <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">
                                        Correct answer: <strong x-text="currentQuestion.answer"></strong> — <span x-html="currentQuestion.explanation"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="currentQuestion.type === 'code'">
                                <div>
                                    <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase bg-white/4 text-text-muted border-b border-border">◆ Reference Answer</div>
                                    <div class="p-[14px_18px] font-mono text-[12.5px] leading-[1.7] bg-[#0e0e10] text-white/85 whitespace-pre" x-text="currentQuestion.answer"></div>
                                    <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded"
                                         x-html="currentQuestion.explanation"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <div class="flex justify-between items-center">
                <button @click="skip()" class="px-[22px] py-2.5 rounded-[10px] text-xs font-semibold tracking-[0.05em] uppercase border-[1.5px] border-border text-text-muted hover:border-white/25 hover:text-white transition-all">Skip</button>
                <button @click="revealed ? next() : submit()" class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase transition-all hover:-translate-y-0.5" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))" x-text="revealed ? 'Next Question →' : 'Submit Answer'"></button>
            </div>
        </div>
    </template>

</div>

@script
<script>
    Alpine.data('quizSession', () => ({
        screen: 'setup',
        mode: null,
        modeLabels: { shuffle: 'Shuffle — all types', mc: 'Multiple Choice', fill: 'Fill in the Blank', code: 'Write the Code' },
        modeOptions: [
            { mode: 'shuffle', icon: '⇄', name: 'Shuffle', desc: 'Mix of all question types — keeps your recall sharp and unpredictable.', countLabel: '6 questions', kindLabel: 'All types' },
            { mode: 'mc', icon: '◉', name: 'Multiple Choice', desc: 'Four options, one answer. Good for drilling recognition over recall.', countLabel: '2 questions', kindLabel: 'Pick one' },
            { mode: 'fill', icon: '▭', name: 'Fill in the Blank', desc: 'Complete the sentence. Forces you to pull the exact word from memory.', countLabel: '2 questions', kindLabel: 'Type answer' },
            { mode: 'code', icon: '</>', name: 'Write the Code', desc: 'Open-ended. No hints, no options — write it from scratch.', countLabel: '2 questions', kindLabel: 'Open-ended' },
        ],
        questions: [
            { type: 'mc', diff: 'easy', diffLabel: 'Easy · Multiple Choice', tag: 'Laravel',
              question: 'Which artisan command creates a new database migration?',
              options: ['php artisan make:model', 'php artisan make:migration', 'php artisan migrate:fresh', 'php artisan db:seed'],
              answer: 1, explanation: '<code>make:migration</code> generates a new migration file inside <code>database/migrations/</code>.' },
            { type: 'mc', diff: 'medium', diffLabel: 'Medium · Multiple Choice', tag: 'Git',
              question: 'Which git command rewrites commit history?',
              options: ['git merge', 'git fetch', 'git rebase', 'git cherry-pick'],
              answer: 2, explanation: '<code>git rebase</code> replays commits on top of another branch, rewriting their hashes. <code>git merge</code> preserves the original history.' },
            { type: 'fill', diff: 'medium', diffLabel: 'Medium · Fill in the Blank', tag: 'Blade',
              question: '<code>x-slot</code> goes ____ the component tag, not outside.',
              answer: 'inside', explanation: '<code>x-slot</code> must go <strong>inside</strong> the component tag so Blade knows which slot to populate.' },
            { type: 'fill', diff: 'easy', diffLabel: 'Easy · Fill in the Blank', tag: 'Laravel',
              question: '<code>dispatchSync()</code> runs a job ____ and waits for it to finish.',
              answer: 'inline', explanation: '<code>dispatchSync()</code> runs the job <strong>inline</strong> (synchronously) and blocks until it completes, unlike <code>dispatch()</code> which queues it.' },
            { type: 'code', diff: 'hard', diffLabel: 'Hard · Write the Code', tag: 'Blade', lang: 'blade',
              question: 'Write a Blade directive that loops over <code>$items</code> and shows "No items" when the collection is empty.',
              answer: '@' + 'forelse($items as $item)\n    <li>' + '{' + '{ $item->name }' + '}</li>\n@' + 'empty\n    <p>No items</p>\n@' + 'endforelse',
              explanation: '<code>@' + 'forelse</code> combines the loop and the empty state — no need for a separate <code>@' + 'if(count(...))</code> check.' },
            { type: 'code', diff: 'hard', diffLabel: 'Hard · Write the Code', tag: 'SQL', lang: 'sql',
              question: 'Write a SQL query to find duplicate emails in a <code>users</code> table.',
              answer: 'SELECT email, COUNT(*) AS cnt\nFROM users\nGROUP BY email\nHAVING COUNT(*) > 1;',
              explanation: 'Group by the column you want to deduplicate, then filter with <code>HAVING COUNT(*) > 1</code> to keep only the duplicated rows.' },
        ],
        pool: [],
        index: 0,
        revealed: false,
        selectedOption: null,
        fillValue: '',
        codeValue: '',
        get currentQuestion() {
            return this.pool.length ? this.pool[this.index % this.pool.length] : null;
        },
        get progressPct() {
            return this.pool.length ? (this.index / this.pool.length * 100) : 0;
        },
        get isCorrectMc() {
            return this.selectedOption === this.currentQuestion.answer;
        },
        get isCorrectFill() {
            return this.fillValue.trim().toLowerCase() === (this.currentQuestion.answer || '').toLowerCase();
        },
        selectMode(mode) {
            this.mode = mode;
        },
        start() {
            if (!this.mode) return;
            this.pool = this.mode === 'shuffle' ? [...this.questions] : this.questions.filter(q => q.type === this.mode);
            this.index = 0;
            this.resetAnswer();
            this.screen = 'active';
        },
        exit() {
            this.screen = 'setup';
        },
        resetAnswer() {
            this.revealed = false;
            this.selectedOption = null;
            this.fillValue = '';
            this.codeValue = '';
        },
        selectOption(i) {
            if (!this.revealed) this.selectedOption = i;
        },
        submit() {
            this.revealed = true;
        },
        next() {
            this.index = this.pool.length ? (this.index + 1) % this.pool.length : 0;
            this.resetAnswer();
        },
        skip() {
            this.next();
        },
    }));
</script>
@endscript
