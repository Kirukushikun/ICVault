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
            <div class="card p-[14px_16px]" wire:key="question-{{ $question->id }}">
                @if ($editingId === $question->id)
                    <form wire:submit="updateQuestion" class="flex flex-col gap-2.5">
                        <textarea wire:model="editPrompt" rows="2" class="bg-transparent border border-border rounded-[8px] px-3 py-2 text-[12.5px] text-white focus:outline-none focus:border-red/50"></textarea>
                        @error('editPrompt') <span class="text-[11px] text-red">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-2 gap-2.5 max-[560px]:grid-cols-1">
                            <select wire:model="editDifficulty" class="bg-card-bg border border-border rounded-[8px] px-3 py-2 text-[12px] text-white focus:outline-none focus:border-red/50">
                                @foreach ($difficulties as $d)
                                    <option value="{{ $d->value }}">{{ ucfirst($d->value) }}</option>
                                @endforeach
                            </select>
                            <select wire:model="editCategoryId" class="bg-card-bg border border-border rounded-[8px] px-3 py-2 text-[12px] text-white focus:outline-none focus:border-red/50">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input wire:model="editAnswer" type="text" placeholder="Answer" class="bg-transparent border border-border rounded-[8px] px-3 py-2 text-[12.5px] text-white focus:outline-none focus:border-red/50" />
                        @error('editAnswer') <span class="text-[11px] text-red">{{ $message }}</span> @enderror

                        @if ($question->type->value === 'multiple_choice')
                            <textarea wire:model="editOptions" rows="3" placeholder="One option per line" class="bg-transparent border border-border rounded-[8px] px-3 py-2 text-[12.5px] text-white focus:outline-none focus:border-red/50"></textarea>
                        @endif

                        <textarea wire:model="editExplanation" rows="2" placeholder="Explanation (optional)" class="bg-transparent border border-border rounded-[8px] px-3 py-2 text-[12.5px] text-white focus:outline-none focus:border-red/50"></textarea>

                        <div class="flex gap-2 justify-end">
                            <button type="button" wire:click="cancelEdit" class="text-[11px] font-semibold tracking-[0.06em] uppercase px-4 py-2 rounded-[8px] border border-border text-text-muted">Cancel</button>
                            <button type="submit" class="text-[11px] font-semibold tracking-[0.06em] uppercase px-4 py-2 rounded-[8px] text-white" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">Save</button>
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
