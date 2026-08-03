@php
    $status = $lastBatch?->status->value;
    $failed = $status === 'failed';

    // The stepper collapses the batch's five states into the three the reader
    // actually acts on: it parsed, they're reviewing, it landed in the pool.
    $steps = [
        ['label' => 'Parsed', 'state' => ! $failed && in_array($status, ['parsed', 'ready', 'imported'], true) ? 'done' : 'todo'],
        ['label' => 'Reviewing', 'state' => match ($status) {
            'ready' => 'active',
            'imported' => 'done',
            default => 'todo',
        }],
        ['label' => 'Saved to pool', 'state' => $status === 'imported' ? 'done' : 'todo'],
    ];

    // Each tab is the same three controls wearing different words: what the
    // reader is handing over changes, so the labels, the info panel and the
    // button all have to change with it.
    $tabs = [
        'markdown' => [
            'tab' => 'Ready-made',
            'mode' => 'manual',
            'contentLabel' => 'Add Content',
            'dropBig' => 'Drop a .md file, or click to browse',
            'dropSmall' => 'Structured Markdown — Q / options / Answer',
            'info' => 'What happens with a ready-made file?',
            'button' => 'Parse &amp; Review →',
        ],
        'notes' => [
            'tab' => 'Study Notes',
            'mode' => 'ai',
            'contentLabel' => 'Add a Note',
            'dropBig' => 'Drop a .md note, or click to browse',
            'dropSmall' => 'Unformatted is fine — the AI reads it for you',
            'info' => 'What does the AI do with my notes?',
            'button' => 'Generate Questions →',
        ],
        'session' => [
            'tab' => 'Quick Capture',
            'mode' => 'ai',
            'contentLabel' => 'Capture an Idea',
            'info' => 'What does the AI do with a quick capture?',
            'button' => 'Generate Questions →',
        ],
    ];

    $typeOptions = ['multiple_choice' => 'Multiple Choice', 'fill_blank' => 'Fill in the Blank', 'code' => 'Code'];
    $field = 'w-full bg-white/3 border border-border rounded-[8px] px-3 py-2.5 text-[12.5px] text-white focus:outline-none focus:border-red transition-colors';
    $ghost = 'px-[15px] py-[7px] rounded-[7px] border border-border text-[11.5px] font-semibold text-text-muted transition-colors';
    $drop = 'block p-[30px_20px] text-center border-[1.5px] border-dashed border-border rounded-xl bg-white/1.5 cursor-pointer hover:border-red/40 hover:bg-white/3 transition-colors';
    $guide = 'mt-2.5 rounded-[8px] border border-border bg-white/3 px-4 py-1 text-[12px] text-text-muted [&>div]:py-2 [&>div:not(:last-child)]:border-b [&>div]:border-border [&_code]:bg-white/8 [&_code]:text-red [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-[11.5px] [&_code]:font-mono';
@endphp

<div x-data="{ tab: @entangle('tab'), infoOpen: false }">
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Pipeline</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Import &amp; Session Logs</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[620px]">
            Bring in questions three ways: paste ones you've already written, hand over a full note, or drop a quick
            "today I learned." Everything lands here for review before it joins the pool.
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,430px)_minmax(0,1fr)] gap-8 items-start">

        {{-- ─────────── input column ─────────── --}}
        <div class="lg:sticky lg:top-6">
            <div class="flex gap-1 p-1 rounded-xl bg-white/3 border border-border">
                @foreach ($tabs as $key => $config)
                    @php $needsAi = $config['mode'] === 'ai'; @endphp
                    <button type="button" @click="if (@js(! $needsAi || $aiEnabled)) tab = '{{ $key }}'"
                            class="flex-1 flex flex-col items-center gap-1 px-2 py-2.5 rounded-lg text-[11.5px] font-semibold leading-[1.2] transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="tab === '{{ $key }}' ? 'text-white' : 'text-text-muted hover:text-white'"
                            :style="tab === '{{ $key }}' ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''"
                            @disabled($needsAi && ! $aiEnabled)>
                        {{ $config['tab'] }}
                        <span class="text-[9px] font-bold tracking-[0.08em] uppercase px-1.5 py-px rounded-full transition-colors"
                              :class="tab === '{{ $key }}' ? 'bg-white/15 text-white' : '{{ $needsAi ? 'bg-ai/15 text-ai' : 'bg-green/15 text-[#4fcf95]' }}'">
                            {{ $needsAi ? 'AI' : 'Manual' }}
                        </span>
                    </button>
                @endforeach
            </div>

            @unless ($aiEnabled)
                <div class="text-[11px] text-text-muted mt-2.5 leading-relaxed">
                    AI generation is off (<code class="text-red">AI_IMPORT_ENABLED=false</code>) — Ready-made still works.
                </div>
            @endunless
            @error('tab') <div class="text-[11px] text-red mt-2.5">{{ $message }}</div> @enderror

            <x-section-label class="mt-5.5 mb-2">Category</x-section-label>
            <select wire:model="categoryId" class="{{ $field }}">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror

            @foreach ($tabs as $key => $config)
                <x-section-label class="mt-5.5 mb-2" x-show="tab === '{{ $key }}'" x-cloak>{{ $config['contentLabel'] }}</x-section-label>
            @endforeach

            {{-- upload path: ready-made + study notes --}}
            <div x-show="tab === 'markdown'" x-cloak>
                <label class="{{ $drop }}">
                    <input type="file" wire:model="markdownFile" accept=".md" class="hidden">
                    <div class="text-2xl mb-2">📥</div>
                    <div class="text-[13px] font-semibold mb-0.5">
                        {{ $markdownFile ? $markdownFile->getClientOriginalName() : $tabs['markdown']['dropBig'] }}
                    </div>
                    <div class="text-[12px] text-text-muted">{{ $tabs['markdown']['dropSmall'] }}</div>
                </label>
                @error('markdownFile') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
            </div>

            <div x-show="tab === 'notes'" x-cloak>
                <label class="{{ $drop }}">
                    <input type="file" wire:model="noteFile" accept=".md" class="hidden">
                    <div class="text-2xl mb-2">📥</div>
                    <div class="text-[13px] font-semibold mb-0.5">
                        {{ $noteFile ? $noteFile->getClientOriginalName() : $tabs['notes']['dropBig'] }}
                    </div>
                    <div class="text-[12px] text-text-muted">{{ $tabs['notes']['dropSmall'] }}</div>
                </label>
                @error('noteFile') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
            </div>

            {{-- capture path: quick capture --}}
            <div x-show="tab === 'session'" x-cloak>
                <textarea wire:model="sessionLogText" class="{{ $field }} min-h-[150px] text-[13px] leading-[1.65] resize-y"
                          placeholder="Today I learned that composer dump-autoload -o builds a static class map so autoloading is faster in production…

Drop a thought, a definition, or how something works. One idea or several — the AI will sort them into questions."></textarea>
                @error('sessionLogText') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
            </div>

            {{-- info toggle: one button, a panel per tab --}}
            <button type="button" @click="infoOpen = ! infoOpen"
                    class="inline-flex items-center gap-1.5 mt-2.5 text-[12px] text-text-muted hover:text-red transition-colors">
                <span>ⓘ</span>
                @foreach ($tabs as $key => $config)
                    <span x-show="tab === '{{ $key }}'" x-cloak>{{ $config['info'] }}</span>
                @endforeach
            </button>

            <div x-show="infoOpen && tab === 'markdown'" x-cloak class="{{ $guide }}">
                <div><code>Q:</code> starts every question</div>
                <div><code>a) b) c)</code> lettered options make it multiple choice</div>
                <div>A fenced code block makes it a code question</div>
                <div><code>Answer:</code> alone makes it fill-in-the-blank</div>
                <div><code>Explanation:</code> and <code>Difficulty:</code> work anywhere</div>
            </div>
            <div x-show="infoOpen && tab === 'notes'" x-cloak class="{{ $guide }}">
                <div>Give it a full note — headings, paragraphs, code blocks, or none of that. It doesn't need formatting.</div>
                <div>The AI scans for facts worth quizzing and drafts a question for each.</div>
                <div>It writes plausible wrong answers and sets a difficulty per question.</div>
                <div>Every draft shows the note excerpt it came from, so you can check it.</div>
                <div>Nothing saves until you approve it — edit or discard any draft.</div>
            </div>
            <div x-show="infoOpen && tab === 'session'" x-cloak class="{{ $guide }}">
                <div>Type a loose "today I learned" — even a single sentence works.</div>
                <div>The AI turns each idea into a clean, self-contained question.</div>
                <div>It fills in options and a difficulty guess where they're missing.</div>
                <div>Rougher input means rougher drafts — expect to edit more here.</div>
                <div>You review and approve everything before it joins the pool.</div>
            </div>

            {{-- pasting only makes sense for questions already written out --}}
            <div x-show="tab === 'markdown'" x-cloak>
                <x-section-label class="mt-5.5 mb-2">Or Paste Below</x-section-label>
                <textarea wire:model="markdownText" spellcheck="false"
                          class="{{ $field }} min-h-[170px] font-mono text-[12.5px] leading-[1.7] resize-y"
                          placeholder="Q: What does PSR-4 define?
a) Autoloading standard
b) Coding style guide
Answer: a
Explanation: PSR-4 maps namespaces to file paths."></textarea>
            </div>

            <button type="button" wire:click="generate" wire:loading.attr="disabled"
                    class="w-full mt-5.5 px-[22px] py-3.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase hover:-translate-y-0.5 transition-transform disabled:opacity-50 disabled:translate-y-0"
                    style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
                <span wire:loading.remove wire:target="generate">
                    @foreach ($tabs as $key => $config)
                        <span x-show="tab === '{{ $key }}'" x-cloak>{!! $config['button'] !!}</span>
                    @endforeach
                </span>
                <span wire:loading wire:target="generate">Working…</span>
            </button>
        </div>

        {{-- ─────────── review column ─────────── --}}
        <div>
            <div class="flex items-baseline justify-between mb-1">
                <x-section-label class="mb-0">Review Candidates</x-section-label>
                @if (count($candidates))
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-red/15 text-[#ec5c86]">{{ count($candidates) }} pending</span>
                @endif
            </div>
            <div class="text-[12.5px] text-text-muted">Nothing here is saved yet. Edit anything, discard what you don't want, then save.</div>

            @if ($failed)
                <div class="flex items-start gap-3 mt-3.5 px-4 py-3.5 rounded-xl border border-red/30 bg-red/8">
                    <div class="text-red text-sm leading-none mt-0.5">✗</div>
                    <div>
                        <div class="text-[12px] font-semibold text-red mb-0.5">Generation failed</div>
                        <div class="text-[12px] text-white/80 leading-relaxed">{{ $lastBatch->error_message }}</div>
                    </div>
                </div>
            @else
                <div class="flex mt-3.5 mb-5.5 rounded-[9px] border border-border bg-white/3 overflow-hidden">
                    @foreach ($steps as $index => $step)
                        <div @class(['flex-1 flex items-center gap-2.5 px-4 py-3', 'border-l border-border' => $index > 0])>
                            <span @class([
                                'w-5 h-5 rounded-full grid place-items-center text-[11px] font-bold shrink-0',
                                'bg-green text-white' => $step['state'] === 'done',
                                'bg-red text-white' => $step['state'] === 'active',
                                'bg-white/5 text-text-muted border border-border' => $step['state'] === 'todo',
                            ])>{{ $step['state'] === 'done' ? '✓' : $index + 1 }}</span>
                            <span @class(['text-[12px] font-semibold', 'text-text-muted' => $step['state'] === 'todo'])>{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @forelse ($candidates as $i => $candidate)
                <div @class(['card p-[18px] mb-4', 'border-l-2 border-l-ai' => $candidate['isAi']]) wire:key="candidate-{{ $i }}">
                    <div class="flex items-center justify-between mb-3">
                        <span @class([
                            'inline-flex items-center gap-1.5 text-[10px] font-bold tracking-[0.08em] uppercase px-2.5 py-[3px] rounded-full',
                            'bg-ai/15 text-ai' => $candidate['isAi'],
                            'bg-green/15 text-[#4fcf95]' => ! $candidate['isAi'],
                        ])>{{ $candidate['isAi'] ? '✦ AI-generated' : '✓ From your file' }}</span>
                        <span class="text-[11px] font-semibold text-text-muted">{{ $i + 1 }} of {{ count($candidates) }}</span>
                    </div>

                    <div class="flex gap-2.5 mb-3.5">
                        <select wire:model.live="candidates.{{ $i }}.type" class="{{ $field }} py-2">
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select wire:model="candidates.{{ $i }}.difficulty" class="{{ $field }} py-2">
                            @foreach (['easy', 'medium', 'hard'] as $d)
                                <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error("candidates.{$i}.type") <div class="text-[11px] text-red mb-3">{{ $message }}</div> @enderror

                    <x-section-label class="mb-1.5">Question</x-section-label>
                    <textarea wire:model="candidates.{{ $i }}.prompt" rows="2"
                              class="{{ $field }} font-semibold text-[13.5px] leading-[1.5] resize-y"></textarea>
                    @error("candidates.{$i}.prompt") <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror

                    @if ($candidate['type'] === 'multiple_choice')
                        <x-section-label class="mt-3.5 mb-1.5">Options — click to mark the correct one</x-section-label>
                        <div class="flex flex-col gap-1.5">
                            @foreach ($candidate['options'] as $j => $option)
                                @php $isCorrect = $candidate['answerIndex'] === $j; @endphp
                                <div wire:key="candidate-{{ $i }}-option-{{ $j }}"
                                     @class([
                                        'flex items-center gap-2.5 pl-3 pr-2 py-1.5 rounded-[8px] border transition-colors',
                                        'bg-green/10 border-green/50' => $isCorrect,
                                        'bg-white/3 border-border' => ! $isCorrect,
                                     ])>
                                    <button type="button" wire:click="markCorrect({{ $i }}, {{ $j }})"
                                            title="Mark as the correct answer"
                                            @class([
                                                'w-[18px] h-[18px] rounded-full shrink-0 grid place-items-center text-[11px] font-bold leading-none transition-colors',
                                                'bg-green text-white' => $isCorrect,
                                                'border-[1.5px] border-text-muted hover:border-green' => ! $isCorrect,
                                            ])>{{ $isCorrect ? '✓' : '' }}</button>
                                    <input type="text" wire:model="candidates.{{ $i }}.options.{{ $j }}"
                                           placeholder="Option text"
                                           class="flex-1 bg-transparent border-0 py-1 text-[13px] text-white focus:outline-none" />
                                    @if ($isCorrect)
                                        <span class="text-[9.5px] font-bold tracking-[0.1em] uppercase text-[#4fcf95]">Correct</span>
                                    @endif
                                    <button type="button" wire:click="removeOption({{ $i }}, {{ $j }})"
                                            title="Remove this option"
                                            class="w-6 h-6 shrink-0 rounded-[6px] text-text-muted hover:text-red hover:bg-white/5 transition-colors">×</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" wire:click="addOption({{ $i }})" class="mt-2 text-[11.5px] text-text-muted hover:text-red transition-colors">+ Add option</button>
                        @error("candidates.{$i}.options") <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
                    @else
                        <x-section-label class="mt-3.5 mb-1.5">Answer</x-section-label>
                        <textarea wire:model="candidates.{{ $i }}.answer" rows="{{ $candidate['type'] === 'code' ? 4 : 2 }}"
                                  @class([$field, 'resize-y leading-[1.6]', 'font-mono text-[12px]' => $candidate['type'] === 'code'])></textarea>
                        @error("candidates.{$i}.answer") <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
                    @endif

                    <x-section-label class="mt-3.5 mb-1.5">Explanation</x-section-label>
                    <textarea wire:model="candidates.{{ $i }}.explanation" rows="2" placeholder="Optional — worth knowing after answering"
                              class="{{ $field }} text-[12.5px] text-text-muted leading-[1.6] resize-y"></textarea>

                    @if ($candidate['excerpt'])
                        <div class="mt-3 rounded-[8px] border border-ai/25 bg-ai/8 px-3 py-2.5">
                            <div class="text-[9.5px] font-bold tracking-[0.1em] uppercase text-ai mb-1">Drawn from your note</div>
                            <div class="text-[12px] italic text-white/65 leading-relaxed">"{{ $candidate['excerpt'] }}"</div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 mt-3.5 pt-3.5 border-t border-border">
                        <button type="button" wire:click="discardCandidate({{ $i }})"
                                class="{{ $ghost }} hover:border-red/60 hover:text-red">Discard</button>
                        <button type="button" wire:click="saveCandidate({{ $i }})" wire:loading.attr="disabled"
                                class="{{ $ghost }} hover:border-green/60 hover:text-[#4fcf95]">Save this one</button>
                    </div>
                </div>
            @empty
                <div class="card p-8 text-center text-[12.5px] text-text-muted">
                    @if ($status === 'imported')
                        Every candidate from that batch is in the pool. Bring in more to keep going.
                    @elseif ($failed)
                        Nothing to review — fix the problem above and try again.
                    @else
                        Questions land here for review before anything is saved.
                    @endif
                </div>
            @endforelse

            @if (count($candidates) > 1)
                <button type="button" wire:click="saveCandidates" wire:loading.attr="disabled"
                        class="w-full px-[22px] py-3 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase hover:-translate-y-0.5 transition-transform disabled:opacity-50 disabled:translate-y-0"
                        style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
                    Save All {{ count($candidates) }} to Pool
                </button>
            @endif

            <x-section-label class="mt-8 mb-3">Generated This Session</x-section-label>
            <div class="flex flex-col gap-2.5">
                @forelse ($generatedQuestions as $question)
                    <x-generated-item :difficulty="$question->difficulty->value" :tag="$question->category->name">{!! $question->prompt !!}</x-generated-item>
                @empty
                    <div class="text-[12px] text-text-muted">Nothing generated yet this session.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
