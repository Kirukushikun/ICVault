<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Quiz Vault</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Quiz Settings</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Drill preferences, reminders, and everything that reads or writes your question pool.
        </div>
    </div>

    {{-- Quiz Preferences --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Preferences</div>

        <div class="flex flex-col items-start gap-2.5 py-3.5 border-b border-white/5">
            <div class="flex items-center gap-4 w-full">
                <div class="w-7 flex justify-center opacity-75"><x-lucide-arrow-left-right class="w-[18px] h-[18px]" /></div>
                <div class="flex-1">
                    <div class="text-[13.5px] font-medium">Default Mode</div>
                    <div class="text-[11px] text-text-muted">Which mode is preselected when you open a session</div>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap pl-11">
                @foreach (['shuffle' => 'Shuffle', 'mc' => 'Multiple Choice', 'fill' => 'Fill in the Blank', 'code' => 'Write the Code'] as $value => $label)
                    <button wire:click="$set('prefDefaultMode', '{{ $value }}')"
                            class="text-[10.5px] font-semibold tracking-[0.07em] uppercase px-3.5 py-1.5 rounded-full border-[1.5px] transition-all"
                            @class(['bg-red/12 border-red/40 text-[#ec5c86]' => $prefDefaultMode === $value, 'border-border text-text-muted hover:border-white/18 hover:text-white' => $prefDefaultMode !== $value])
                    >{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-clipboard-list class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Daily Quota</div>
                <div class="text-[11px] text-text-muted">Number of questions to complete each day — applies from tomorrow's session onward</div>
            </div>
            <div class="flex items-center gap-2.5">
                <button wire:click="$set('prefDailyQuota', {{ max(1, $prefDailyQuota - 1) }})" class="w-[26px] h-[26px] rounded-full border border-border text-text-muted hover:border-white/22 hover:text-white flex items-center justify-center">−</button>
                <span class="text-sm font-semibold min-w-[22px] text-center">{{ $prefDailyQuota }}</span>
                <button wire:click="$set('prefDailyQuota', {{ min(30, $prefDailyQuota + 1) }})" class="w-[26px] h-[26px] rounded-full border border-border text-text-muted hover:border-white/22 hover:text-white flex items-center justify-center">+</button>
            </div>
        </div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-lightbulb class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Auto-reveal Answers</div>
                <div class="text-[11px] text-text-muted">Show the correct answer immediately on submit, instead of requiring a separate "Reveal" step</div>
            </div>
            <x-toggle-switch wire:model.live="prefAutoReveal" />
        </div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-dices class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Shuffle Question Order</div>
                <div class="text-[11px] text-text-muted">Randomize the sequence instead of serving oldest-first</div>
            </div>
            <x-toggle-switch wire:model.live="prefShuffleOrder" />
        </div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-tag class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Show Difficulty Badge</div>
                <div class="text-[11px] text-text-muted">Display Easy / Medium / Hard label on each question</div>
            </div>
            <x-toggle-switch wire:model.live="prefShowDifficulty" />
        </div>

        <div class="flex items-center gap-4 py-3.5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-timer class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Timed Mode</div>
                <div class="text-[11px] text-text-muted">Each question has a 60-second countdown that auto-submits when it runs out</div>
            </div>
            <x-toggle-switch wire:model.live="prefTimedMode" />
        </div>
    </div>

    {{-- Categories --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Categories</div>

        <div class="flex flex-col gap-1.5 mb-4">
            @forelse ($categories as $category)
                <div class="flex items-center gap-3 py-2 px-3 rounded-lg border border-white/5" wire:key="category-{{ $category->id }}">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $category->color }}"></span>
                    <span class="text-[13px] font-medium flex-1">{{ $category->name }}</span>
                    <span class="text-[11px] text-text-muted">{{ $category->questions_count }} question{{ $category->questions_count === 1 ? '' : 's' }}</span>
                    <button
                        wire:click="deleteCategory({{ $category->id }})"
                        wire:confirm="Delete '{{ $category->name }}'?{{ $category->questions_count ? ' This also deletes its '.$category->questions_count.' question(s).' : '' }}"
                        class="text-[11px] text-red/70 hover:text-red shrink-0"
                    >Delete</button>
                </div>
            @empty
                <div class="text-[12px] text-text-muted">No categories yet.</div>
            @endforelse
        </div>

        <form wire:submit="addCategory" class="flex items-center gap-2.5 flex-wrap">
            <input type="color" wire:model="newCategoryColor" class="w-9 h-9 rounded-lg border border-border bg-transparent cursor-pointer p-0.5" />
            <input type="text" wire:model="newCategoryName" placeholder="New category name…"
                   class="flex-1 min-w-[180px] bg-transparent border border-border rounded-lg px-3.5 py-2 text-[12.5px] text-white placeholder:text-text-muted focus:outline-none focus:border-red/50" />
            <button type="submit" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/45 bg-red/13 text-[#ec5c86] hover:border-red hover:bg-red/28 hover:text-white transition-colors">Add Category</button>
        </form>
        @error('newCategoryName') <div class="text-[11px] text-[#ec5c86] mt-2">{{ $message }}</div> @enderror
        @error('newCategoryColor') <div class="text-[11px] text-[#ec5c86] mt-2">{{ $message }}</div> @enderror
    </div>

    {{-- Notifications --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-2 after:content-[''] after:flex-1 after:h-px after:bg-border">Notifications</div>
        <div class="text-[11px] text-text-muted mb-3.5">Your choice is saved, but delivery isn't wired up yet — these don't send anything until a channel (email, browser push) is configured.</div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-flame class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Streak Reminder</div>
                <div class="text-[11px] text-text-muted">Alert when you haven't answered today's quota yet</div>
            </div>
            <x-toggle-switch wire:model.live="prefStreakReminder" />
        </div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-bar-chart-3 class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Weekly Summary</div>
                <div class="text-[11px] text-text-muted">Send a recap of recall rate and mastery progress every Monday</div>
            </div>
            <x-toggle-switch wire:model.live="prefWeeklySummary" />
        </div>

        <div class="flex items-center gap-4 py-3.5">
            <div class="w-7 flex justify-center opacity-75"><x-lucide-sparkles class="w-[18px] h-[18px]" /></div>
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">New Questions Added</div>
                <div class="text-[11px] text-text-muted">Notify when the import pipeline generates new cards</div>
            </div>
            <x-toggle-switch wire:model.live="prefNewQuestionsNotif" />
        </div>
    </div>

    {{-- Backup & Restore --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Backup &amp; Restore</div>

        <div class="flex flex-col items-start gap-0 pb-5.5">
            <div class="flex items-center gap-4 w-full mb-2.5">
                <div class="w-7 flex justify-center opacity-75"><x-lucide-download class="w-[18px] h-[18px]" /></div>
                <div class="flex-1">
                    <div class="text-[13.5px] font-medium">Export Pool</div>
                    <div class="text-[11px] text-text-muted">Download your question pool as JSON — doubles as a portable backup</div>
                </div>
            </div>
            <div class="flex gap-2.5 flex-wrap ml-11 mb-4">
                <span class="text-[11px] text-text-muted"><strong class="text-white font-semibold">{{ $questionCount }}</strong> questions</span>
                <span class="text-[11px] text-text-muted before:content-['·'] before:opacity-30 before:mr-1.5"><strong class="text-white font-semibold">{{ $categoryCount }}</strong> categories</span>
                <span class="text-[11px] text-text-muted before:content-['·'] before:opacity-30 before:mr-1.5"><strong class="text-white font-semibold">{{ $sessionCount }}</strong> sessions</span>
                <span class="text-[11px] text-text-muted before:content-['·'] before:opacity-30 before:mr-1.5"><strong class="text-white font-semibold">{{ $avgRecall }}%</strong> avg recall</span>
            </div>
            <div class="flex gap-2.5 flex-wrap ml-11">
                <button wire:click="exportJson" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 bg-white/3 text-xs font-semibold text-text-muted hover:border-cyan-400/35 hover:bg-cyan-400/5 hover:text-white transition-colors">
                    <span class="text-[9px] font-bold tracking-[0.1em] px-1.5 py-0.5 rounded bg-cyan-400/14 text-cyan-400">JSON</span> Full Backup
                </button>
                <button wire:click="exportCsv" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 bg-white/3 text-xs font-semibold text-text-muted hover:border-green/35 hover:bg-green/5 hover:text-white transition-colors">
                    <span class="text-[9px] font-bold tracking-[0.1em] px-1.5 py-0.5 rounded bg-green/14 text-[#4fcf95]">CSV</span> Questions Only
                </button>
                <button wire:click="exportMarkdown" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 bg-white/3 text-xs font-semibold text-text-muted hover:border-red/35 hover:bg-red/5 hover:text-[#ec5c86] transition-colors">
                    <span class="text-[9px] font-bold tracking-[0.1em] px-1.5 py-0.5 rounded bg-red/14 text-[#ec5c86]">.md</span> Obsidian Format
                </button>
            </div>
        </div>

        <div class="flex flex-col items-start gap-0">
            <div class="flex items-center gap-4 w-full mb-2.5">
                <div class="w-7 flex justify-center opacity-75"><x-lucide-folder-open class="w-[18px] h-[18px]" /></div>
                <div class="flex-1">
                    <div class="text-[13.5px] font-medium">Import / Restore</div>
                    <div class="text-[11px] text-text-muted">Upload a previous vault export or import questions from another format</div>
                </div>
            </div>

            <label class="flex flex-col items-center gap-1 w-full mt-1 border-[1.5px] border-dashed border-white/10 rounded-xl p-[22px_20px] text-center cursor-pointer relative hover:border-red/35 hover:bg-red/3 transition-colors">
                <input type="file" accept=".json" wire:model="importFile" class="absolute inset-0 opacity-0 cursor-pointer" />
                <div class="text-[13px] font-medium">Drop a backup file or click to browse</div>
                <div class="text-[11px] text-text-muted">Accepts .json (full vault export)</div>
                <div class="flex justify-center gap-1.5 mt-2.5">
                    <span class="text-[9.5px] font-bold tracking-[0.1em] px-2.5 py-1 rounded-full bg-cyan-400/14 text-cyan-400">.json</span>
                </div>
            </label>
            @error('importFile') <div class="text-[11px] text-[#ec5c86] mt-2 ml-11">{{ $message }}</div> @enderror

            @if ($importFile)
                <div class="flex items-center gap-2.5 mt-2.5 ml-11 px-3.5 py-2 rounded-lg bg-red/6 border border-red/20 text-xs w-fit">
                    <x-lucide-file-text class="w-3.5 h-3.5" />
                    <span class="font-medium">{{ $importFile->getClientOriginalName() }}</span>
                    <button wire:click="$set('importFile', null)" class="text-text-muted hover:text-[#ec5c86] ml-1"><x-lucide-x class="w-3.5 h-3.5" /></button>
                </div>
            @endif

            <div class="flex items-center gap-4 mt-3.5">
                <span class="text-[11px] text-text-muted">On conflict:</span>
                <div class="flex gap-3.5">
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer" :class="$wire.importMode === 'merge' ? 'text-white' : 'text-text-muted'">
                        <input type="radio" wire:model="importMode" value="merge" class="hidden" />
                        <span class="w-[13px] h-[13px] rounded-full border-[1.5px] flex items-center justify-center" :class="$wire.importMode === 'merge' ? 'border-red' : 'border-white/20'">
                            <span x-show="$wire.importMode === 'merge'" class="w-[5px] h-[5px] rounded-full bg-red"></span>
                        </span>
                        Merge
                    </label>
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer" :class="$wire.importMode === 'replace' ? 'text-white' : 'text-text-muted'">
                        <input type="radio" wire:model="importMode" value="replace" class="hidden" />
                        <span class="w-[13px] h-[13px] rounded-full border-[1.5px] flex items-center justify-center" :class="$wire.importMode === 'replace' ? 'border-red' : 'border-white/20'">
                            <span x-show="$wire.importMode === 'replace'" class="w-[5px] h-[5px] rounded-full bg-red"></span>
                        </span>
                        Replace All
                    </label>
                </div>
                <button wire:click="runImport" :disabled="!$wire.importFile"
                        class="ml-auto shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4.5 py-2 rounded-lg border-[1.5px] transition-all"
                        :class="$wire.importFile ? 'text-white border-red/55 bg-red/18 hover:bg-red/28 hover:border-red/75 cursor-pointer' : 'text-white/30 border-white/8 bg-white/3 pointer-events-none'">
                    Import
                </button>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Danger Zone</div>

        <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
            <div class="flex-1">
                <div class="text-[13.5px] font-medium">Reset Streak</div>
                <div class="text-[11px] text-text-muted">Set your current streak back to zero. This can't be undone.</div>
            </div>
            <button wire:click="resetStreak" wire:confirm="Reset your current streak to zero?" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/30 bg-red/8 text-red/75 hover:border-red/65 hover:bg-red/18 hover:text-[#ec5c86] transition-colors">Reset Streak</button>
        </div>
        <div class="flex flex-col gap-0 py-3.5 border-b border-white/5">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="text-[13.5px] font-medium">Clear Progress Data</div>
                    <div class="text-[11px] text-text-muted">Wipe all mastery scores and session history across every category — {{ $attemptCount }} attempt(s) across {{ $sessionCount }} session(s).</div>
                </div>
                @if (! $confirmingClear)
                    <button wire:click="startClearConfirm" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/30 bg-red/8 text-red/75 hover:border-red/65 hover:bg-red/18 hover:text-[#ec5c86] transition-colors">Clear Progress</button>
                @endif
            </div>
            @if ($confirmingClear)
                <div class="flex items-center gap-2.5 mt-3">
                    <input type="text" wire:model="clearConfirmText" placeholder="Type RESET to confirm" class="flex-1 bg-transparent border border-red/30 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-red/60" />
                    <button wire:click="confirmClear" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/55 bg-red/18 text-white hover:bg-red/28">Confirm</button>
                    <button wire:click="cancelClearConfirm" class="shrink-0 text-[11px] text-text-muted hover:text-white uppercase tracking-[0.06em]">Cancel</button>
                </div>
                @error('clearConfirmText') <div class="text-[11px] text-[#ec5c86] mt-1.5">{{ $message }}</div> @enderror
            @endif
        </div>
        <div class="flex flex-col gap-0 py-3.5">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="text-[13.5px] font-medium">Delete All Questions</div>
                    <div class="text-[11px] text-text-muted">Remove every card from the question pool — {{ $questionCount }} question(s). Your vault will be empty.</div>
                </div>
                @if (! $confirmingDelete)
                    <button wire:click="startDeleteConfirm" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/45 bg-red/13 text-[#ec5c86] hover:border-red hover:bg-red/28 hover:text-white transition-colors">Delete All</button>
                @endif
            </div>
            @if ($confirmingDelete)
                <div class="flex items-center gap-2.5 mt-3">
                    <input type="text" wire:model="deleteConfirmText" placeholder="Type DELETE to confirm" class="flex-1 bg-transparent border border-red/30 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-red/60" />
                    <button wire:click="confirmDelete" class="shrink-0 text-[11px] font-bold tracking-[0.09em] uppercase px-4 py-2 rounded-lg border-[1.5px] border-red/55 bg-red/18 text-white hover:bg-red/28">Confirm</button>
                    <button wire:click="cancelDeleteConfirm" class="shrink-0 text-[11px] text-text-muted hover:text-white uppercase tracking-[0.06em]">Cancel</button>
                </div>
                @error('deleteConfirmText') <div class="text-[11px] text-[#ec5c86] mt-1.5">{{ $message }}</div> @enderror
            @endif
        </div>
    </div>
</div>
