<div>

    @if ($screen === 'setup')
        <div>
            <div class="mb-7">
                <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Quiz Session</div>
                <div class="font-display text-[28px] font-bold tracking-wide">Choose a Mode</div>
                <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
                    Pick how you want to be tested — or go full shuffle to keep it unpredictable.
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5 mb-7">
                @foreach ($modeOptions as $option)
                    <div wire:click="selectMode('{{ $option['mode'] }}')"
                         class="p-[24px_22px] rounded-2xl border-[1.5px] bg-card-bg cursor-pointer transition-all relative overflow-hidden {{ $mode === $option['mode'] ? 'border-red shadow-[0_8px_28px_rgba(195,7,63,0.18)] -translate-y-1' : 'border-border hover:border-red/35 hover:-translate-y-0.5' }}">
                        <span class="text-[22px] mb-3.5 block">{{ $option['icon'] }}</span>
                        <div class="font-display text-base font-bold tracking-wide mb-1.5">{{ $option['name'] }}</div>
                        <div class="text-[11.5px] text-text-muted leading-[1.55] mb-3.5">{{ $option['desc'] }}</div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9.5px] font-semibold tracking-[0.12em] uppercase px-2.5 py-1 rounded-full border {{ $mode === $option['mode'] ? 'border-red/35 text-[#ec5c86] bg-red/8' : 'border-border text-text-muted' }}">{{ $option['countLabel'] }}</span>
                            <span class="text-[9.5px] font-semibold tracking-[0.12em] uppercase px-2.5 py-1 rounded-full border {{ $mode === $option['mode'] ? 'border-red/35 text-[#ec5c86] bg-red/8' : 'border-border text-text-muted' }}">{{ $option['kindLabel'] }}</span>
                            <span class="ml-auto w-5 h-5 rounded-full bg-red flex items-center justify-center text-[10px] transition-all {{ $mode === $option['mode'] ? 'opacity-100 scale-100' : 'opacity-0 scale-50' }}">✓</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button wire:click="start"
                        class="px-8 py-3.5 rounded-xl text-white text-[13px] font-bold tracking-[0.08em] uppercase transition-all {{ $mode ? 'opacity-100 hover:-translate-y-0.5' : 'opacity-35 pointer-events-none' }}"
                        style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
                    Start Session →
                </button>
            </div>
        </div>
    @else
        <div>
            <div class="mb-5">
                <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Session</div>
                <div class="font-display text-[28px] font-bold tracking-wide">Daily Quiz</div>
                <div class="text-[12.5px] text-text-muted mt-1.5 font-light flex items-center gap-2.5">
                    <span>{{ $modeLabel }}</span>
                    <button wire:click="exit" class="text-[10px] text-text-muted underline tracking-[0.06em]">← Change mode</button>
                </div>
            </div>

            <div class="flex items-center gap-3.5 mb-6">
                <div class="flex-1 h-1.5 rounded-full bg-white/6 overflow-hidden">
                    <div class="h-full rounded-full transition-all" style="width:{{ count($pool) ? $index / count($pool) * 100 : 0 }}%; background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))"></div>
                </div>
                <div class="text-[11px] text-text-muted whitespace-nowrap">{{ $index }} / {{ count($pool) }}</div>
            </div>

            @if ($current)
                <div class="card p-[28px_30px] mb-4.5">
                    <div class="flex items-center justify-between mb-3.5">
                        @if ($showDifficulty)
                            <span class="inline-block text-[9px] font-bold tracking-[0.14em] uppercase px-2.5 py-1 rounded-full
                                  @if ($current['diff'] === 'easy') bg-green/15 text-[#4fcf95]
                                  @elseif ($current['diff'] === 'medium') bg-amber/15 text-[#e0a94a]
                                  @else bg-red/15 text-[#ec5c86] @endif">{{ $current['diffLabel'] }}</span>
                        @else
                            <span></span>
                        @endif

                        @if ($timedMode && ! $answered)
                            <div wire:key="timer-{{ $index }}" x-data="{ secondsLeft: 60 }"
                                 x-init="const t = setInterval(() => { secondsLeft--; if (secondsLeft <= 0) { clearInterval(t); $wire.submit(); } }, 1000); $cleanup(() => clearInterval(t))"
                                 class="text-[11px] font-bold tabular-nums px-2.5 py-1 rounded-full border" :class="secondsLeft <= 10 ? 'border-red/50 text-[#ec5c86] bg-red/10' : 'border-border text-text-muted'">
                                <span x-text="secondsLeft"></span>s
                            </div>
                        @endif
                    </div>
                    <div class="text-[10px] text-text-muted tracking-[0.08em] uppercase mb-2.5">{{ $current['tag'] }}</div>
                    <div class="text-[17px] font-medium leading-[1.5] mb-5.5 [&_code]:font-mono [&_code]:text-sm [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">{!! $current['question'] !!}</div>

                    @if ($current['type'] === 'multiple_choice')
                        <div class="flex flex-col gap-2.5">
                            @foreach ($current['options'] as $i => $opt)
                                <div wire:click="selectOption({{ $i }})"
                                     class="flex items-center gap-3 px-4 py-3.5 rounded-[10px] border-[1.5px] bg-white/2 text-[13px] cursor-pointer transition-all
                                        @if ($revealed && $opt === $current['answer']) border-green bg-green/12 text-[#4fcf95]
                                        @elseif ($revealed && $i === $selectedOption) border-red/60 bg-red/10 text-[#ec5c86]
                                        @elseif (! $answered && $selectedOption === $i) border-red bg-red/10
                                        @else border-border @endif">
                                    <span class="w-[22px] h-[22px] rounded-md flex items-center justify-center text-[10.5px] font-bold bg-white/6 shrink-0">{{ chr(65 + $i) }}</span>
                                    <span>{{ $opt }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($current['type'] === 'fill_blank')
                        <input type="text" wire:model="fillValue" @disabled($answered) placeholder="Type your answer…"
                               class="w-full bg-white/3 border-[1.5px] rounded-[10px] px-4 py-3.5 text-white font-mono text-[13px] focus:outline-none
                                  {{ ! $revealed ? 'border-border focus:border-red' : ($lastCorrect ? 'border-green bg-green/8 text-[#4fcf95]' : 'border-red/50 bg-red/7 text-[#ec5c86]') }}">
                    @else
                        <div class="bg-[#0e0e10] border-[1.5px] border-border rounded-xl overflow-hidden">
                            <div class="flex items-center gap-1.5 px-3.5 py-2.5 bg-white/3 border-b border-border">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f57]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#febc2e]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#28c840]"></span>
                            </div>
                            <textarea wire:model="codeValue" @disabled($answered) rows="5"
                                      class="w-full bg-transparent px-4.5 py-4 font-mono text-[13px] leading-[1.7] text-white/85 focus:outline-none resize-none"></textarea>
                        </div>
                    @endif

                    @if ($revealed)
                        <div class="mt-4 rounded-xl border-[1.5px] border-white/7 overflow-hidden">
                            @if ($current['type'] === 'multiple_choice')
                                <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase {{ $lastCorrect ? 'bg-green/12 text-[#4fcf95] border-b border-green/20' : 'bg-red/10 text-[#ec5c86] border-b border-red/20' }}">{{ $lastCorrect ? '✓ Correct' : '✗ Incorrect' }}</div>
                                <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">{!! $current['explanation'] !!}</div>
                            @elseif ($current['type'] === 'fill_blank')
                                <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase {{ $lastCorrect ? 'bg-green/12 text-[#4fcf95] border-b border-green/20' : 'bg-red/10 text-[#ec5c86] border-b border-red/20' }}">{{ $lastCorrect ? '✓ Correct' : '✗ Incorrect' }}</div>
                                <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">
                                    Correct answer: <strong>{{ $current['answer'] }}</strong> — {!! $current['explanation'] !!}
                                </div>
                            @else
                                <div class="px-4 py-2.5 text-[10px] font-bold tracking-[0.16em] uppercase bg-white/4 text-text-muted border-b border-border">◆ Reference Answer</div>
                                <div class="p-[14px_18px] font-mono text-[12.5px] leading-[1.7] bg-[#0e0e10] text-white/85 whitespace-pre">{{ $current['answer'] }}</div>
                                <div class="p-3.5 text-[13px] leading-[1.55] text-white/82 bg-white/2 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">{!! $current['explanation'] !!}</div>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <div class="card p-6 text-text-muted text-sm text-center">No questions in this mode yet.</div>
            @endif

            <div class="flex justify-between items-center">
                <button wire:click="skip" class="px-[22px] py-2.5 rounded-[10px] text-xs font-semibold tracking-[0.05em] uppercase border-[1.5px] border-border text-text-muted hover:border-white/25 hover:text-white transition-all">Skip</button>
                @if ($revealed)
                    <button wire:click="next" class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase transition-all hover:-translate-y-0.5" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">Next Question →</button>
                @elseif ($answered)
                    <button wire:click="reveal" class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase transition-all hover:-translate-y-0.5" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">Reveal Answer</button>
                @else
                    <button wire:click="submit" class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase transition-all hover:-translate-y-0.5" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">Submit Answer</button>
                @endif
            </div>
        </div>
    @endif

</div>
