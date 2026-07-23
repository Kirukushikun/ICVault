@php
    $pipelineOrder = ['uploaded', 'parsed', 'ai_converted', 'queued', 'imported'];
    $stageReached = fn (string $stage) => $lastBatch
        && $lastBatch->status->value !== 'failed'
        && array_search($lastBatch->status->value, $pipelineOrder) >= array_search($stage, $pipelineOrder);
@endphp

<div x-data="{ tab: @entangle('tab') }">
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Pipeline</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Import &amp; Session Logs</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Feed in Obsidian notes or a quick session dump — the AI pipeline turns it into new quiz questions.
        </div>
    </div>

    <div class="flex gap-2 mb-4.5">
        <button type="button" @click="tab = 'notes'" class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                :class="tab === 'notes' ? 'text-white border-transparent' : 'text-text-muted border-border'"
                :style="tab === 'notes' ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''">
            Obsidian Notes
        </button>
        <button type="button" @click="tab = 'session'" class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                :class="tab === 'session' ? 'text-white border-transparent' : 'text-text-muted border-border'"
                :style="tab === 'session' ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''">
            Session Log
        </button>
    </div>

    <div class="mb-4.5">
        <label class="block text-[10px] font-semibold tracking-[0.1em] uppercase text-text-muted mb-1.5">Category</label>
        <select wire:model="categoryId" class="bg-white/3 border-[1.5px] border-border rounded-xl px-3.5 py-2 text-[12.5px] text-white focus:outline-none focus:border-red">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        @error('categoryId') <div class="text-[11px] text-red mt-1">{{ $message }}</div> @enderror
    </div>

    <div x-show="tab === 'notes'">
        <label class="block p-10 text-center border-[1.5px] border-dashed border-white/15 rounded-2xl bg-white/1.5 mb-4.5 cursor-pointer hover:border-red/35 transition-colors">
            <input type="file" wire:model="noteFile" accept=".md" class="hidden">
            <div class="text-2xl mb-2.5">📥</div>
            <div class="text-[13px] font-medium mb-1">
                @if ($noteFile)
                    {{ $noteFile->getClientOriginalName() }}
                @else
                    Drop .md files here, or click to browse
                @endif
            </div>
            <div class="text-[11px] text-text-muted">Existing Obsidian vault notes — parsed and converted into question candidates</div>
        </label>
        @error('noteFile') <div class="text-[11px] text-red mb-3">{{ $message }}</div> @enderror
    </div>

    <div x-show="tab === 'session'">
        <textarea wire:model="sessionLogText" class="w-full min-h-[160px] bg-white/3 border-[1.5px] border-border rounded-xl px-4.5 py-4 text-white font-mono text-[12.5px] leading-[1.6] resize-y mb-4.5 focus:outline-none focus:border-red"
                  placeholder="What did you run into today?

e.g. x-slot goes inside the component tag, not outside
e.g. this middleware runs before the controller, not after"></textarea>
        @error('sessionLogText') <div class="text-[11px] text-red mb-3">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center gap-2.5 my-5.5 flex-wrap text-[11px] text-text-muted">
        @if ($lastBatch && $lastBatch->status->value === 'failed')
            <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-red/30 bg-red/8 text-red">✗ Failed — {{ $lastBatch->error_message }}</div>
        @else
            <div @class(['flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border', 'border-green/30 bg-green/8 text-[#4fcf95]' => $stageReached('parsed'), 'border-border bg-white/4' => ! $stageReached('parsed')])>
                {{ $stageReached('parsed') ? '✓' : '' }} Parsed
            </div>
            <span class="text-white/20">→</span>
            <div @class(['flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border', 'border-green/30 bg-green/8 text-[#4fcf95]' => $stageReached('ai_converted'), 'border-border bg-white/4' => ! $stageReached('ai_converted')])>
                {{ $stageReached('ai_converted') ? '✓' : '' }} AI Converted
            </div>
            <span class="text-white/20">→</span>
            <div @class(['flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border', 'border-green/30 bg-green/8 text-[#4fcf95]' => $stageReached('imported'), 'border-border bg-white/4' => ! $stageReached('imported')])>
                {{ $stageReached('imported') ? '✓' : '' }} Queued to Pool
            </div>
        @endif
    </div>

    <button type="button" wire:click="generate" wire:loading.attr="disabled"
            class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase hover:-translate-y-0.5 transition-transform disabled:opacity-50"
            style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
        Generate Questions
    </button>

    <x-section-label class="mt-6.5">Generated This Session</x-section-label>
    <div class="flex flex-col gap-2.5">
        @forelse ($generatedQuestions as $question)
            <x-generated-item :difficulty="$question->difficulty->value" :tag="$question->category->name">{!! $question->prompt !!}</x-generated-item>
        @empty
            <div class="text-[12px] text-text-muted">Nothing generated yet this session.</div>
        @endforelse
    </div>
</div>
