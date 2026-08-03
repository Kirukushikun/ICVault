@php
    // Same field and button treatment the import review cards use — editing a
    // question should feel identical wherever you're doing it.
    $field = 'w-full bg-white/3 border border-border rounded-[8px] px-3 py-2.5 text-[12.5px] text-white focus:outline-none focus:border-red transition-colors';
    $ghost = 'px-[15px] py-[7px] rounded-[7px] border border-border text-[11.5px] font-semibold text-text-muted transition-colors';
@endphp

<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Question Pool</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Library</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Drill by category, or shuffle the whole pool.
        </div>
    </div>

    <div class="flex gap-2.5 mb-4.5 max-[720px]:flex-col">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search questions…"
            class="flex-1 bg-transparent border border-border rounded-[10px] px-3.5 py-2 text-[12.5px] text-white placeholder:text-text-muted focus:outline-none focus:border-red/50"
        />
        <select wire:model.live="categoryId" class="bg-card-bg border border-border rounded-[10px] px-3.5 py-2 text-[12.5px] text-white focus:outline-none focus:border-red/50">
            <option value="all">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-2 mb-4.5">
        @foreach (['all' => 'All', 'easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $value => $label)
            <button
                wire:click="$set('difficulty', '{{ $value }}')"
                class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                @class([
                    'text-white border-transparent' => $difficulty === $value,
                    'text-text-muted border-border' => $difficulty !== $value,
                ])
                @style($difficulty === $value ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : '')
            >
                {{ $label }} ({{ $tabCounts[$value] }})
            </button>
        @endforeach
    </div>

    <div class="flex flex-col gap-2.5">
        @forelse ($questions as $question)
            {{-- Browsing wants density; editing wants room to work. --}}
            <div @class(['card', 'p-[18px]' => $editingId === $question->id, 'p-[14px_16px]' => $editingId !== $question->id])
                 wire:key="question-{{ $question->id }}">
                @if ($editingId === $question->id)
                    <form wire:submit="updateQuestion">
                        <div class="flex gap-2.5 mb-3.5 max-[560px]:flex-col">
                            <select wire:model="editDifficulty" class="{{ $field }} py-2">
                                @foreach ($difficulties as $d)
                                    <option value="{{ $d->value }}">{{ ucfirst($d->value) }}</option>
                                @endforeach
                            </select>
                            <select wire:model="editCategoryId" class="{{ $field }} py-2">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-section-label class="mb-1.5">Question</x-section-label>
                        <textarea wire:model="editPrompt" rows="2" class="{{ $field }} font-semibold text-[13.5px] leading-[1.5] resize-y"></textarea>
                        @error('editPrompt') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror

                        @if ($question->type->value === 'multiple_choice')
                            <x-section-label class="mt-3.5 mb-1.5">Options — click to mark the correct one</x-section-label>
                            <div class="flex flex-col gap-1.5">
                                @foreach ($editOptions as $j => $option)
                                    @php $isCorrect = $editAnswerIndex === $j; @endphp
                                    <div wire:key="edit-option-{{ $j }}"
                                         @class([
                                            'flex items-center gap-2.5 pl-3 pr-2 py-1.5 rounded-[8px] border transition-colors',
                                            'bg-green/10 border-green/50' => $isCorrect,
                                            'bg-white/3 border-border' => ! $isCorrect,
                                         ])>
                                        <button type="button" wire:click="markCorrect({{ $j }})"
                                                title="Mark as the correct answer"
                                                @class([
                                                    'w-[18px] h-[18px] rounded-full shrink-0 grid place-items-center text-[11px] font-bold leading-none transition-colors',
                                                    'bg-green text-white' => $isCorrect,
                                                    'border-[1.5px] border-text-muted hover:border-green' => ! $isCorrect,
                                                ])>{{ $isCorrect ? '✓' : '' }}</button>
                                        <input type="text" wire:model="editOptions.{{ $j }}" placeholder="Option text"
                                               class="flex-1 bg-transparent border-0 py-1 text-[13px] text-white focus:outline-none" />
                                        @if ($isCorrect)
                                            <span class="text-[9.5px] font-bold tracking-[0.1em] uppercase text-[#4fcf95]">Correct</span>
                                        @endif
                                        <button type="button" wire:click="removeOption({{ $j }})" title="Remove this option"
                                                class="w-6 h-6 shrink-0 rounded-[6px] text-text-muted hover:text-red hover:bg-white/5 transition-colors">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="addOption" class="mt-2 text-[11.5px] text-text-muted hover:text-red transition-colors">+ Add option</button>
                            @error('editOptions') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
                        @else
                            <x-section-label class="mt-3.5 mb-1.5">Answer</x-section-label>
                            <textarea wire:model="editAnswer" rows="{{ $question->type->value === 'code' ? 4 : 2 }}"
                                      @class([$field, 'resize-y leading-[1.6]', 'font-mono text-[12px]' => $question->type->value === 'code'])></textarea>
                            @error('editAnswer') <div class="text-[11px] text-red mt-1.5">{{ $message }}</div> @enderror
                        @endif

                        <x-section-label class="mt-3.5 mb-1.5">Explanation</x-section-label>
                        <textarea wire:model="editExplanation" rows="2" placeholder="Optional — worth knowing after answering"
                                  class="{{ $field }} text-[12.5px] text-text-muted leading-[1.6] resize-y"></textarea>

                        <div class="flex justify-end gap-2 mt-3.5 pt-3.5 border-t border-border">
                            <button type="button" wire:click="cancelEdit" class="{{ $ghost }} hover:border-red/60 hover:text-red">Cancel</button>
                            <button type="submit" class="{{ $ghost }} hover:border-green/60 hover:text-[#4fcf95]">Save Changes</button>
                        </div>
                    </form>
                @else
                    <div class="flex items-center gap-3">
                        <x-difficulty-badge :difficulty="$question->difficulty->value" class="shrink-0" />
                        <span class="text-[12.5px] flex-1">{{ $question->prompt }}</span>
                        <span class="text-[9.5px] text-text-muted px-2.5 py-1 rounded-full border border-border shrink-0">
                            {{ $question->category?->name ?? 'Uncategorized' }}
                        </span>
                        <button wire:click="editQuestion({{ $question->id }})" class="text-[11px] text-text-muted hover:text-white shrink-0">Edit</button>
                        <button
                            wire:click="deleteQuestion({{ $question->id }})"
                            wire:confirm="Delete this question? This can't be undone."
                            class="text-[11px] text-red/80 hover:text-red shrink-0"
                        >Delete</button>
                    </div>
                @endif
            </div>
        @empty
            <div class="card p-6 text-text-muted text-sm text-center">
                No questions match.
            </div>
        @endforelse
    </div>
</div>
