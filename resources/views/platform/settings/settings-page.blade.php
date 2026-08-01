@php
    $initials = str(auth()->user()->name)->explode(' ')->take(2)->map(fn ($p) => str($p)->substr(0, 1))->implode('');
@endphp

<div x-data="{ passVisible: false }">
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Configuration</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Settings</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Your account and the platform itself. Each tool keeps its own settings alongside it.
        </div>
    </div>

    {{-- Account --}}
    <div class="mb-10">
        <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Account</div>

        <div class="flex items-center gap-4 pb-5.5 mb-1 border-b border-white/5">
            <div class="w-12 h-12 rounded-full shrink-0 flex items-center justify-center font-display text-lg font-bold shadow-[0_0_0_3px_rgba(195,7,63,0.2)]" style="background:linear-gradient(135deg,var(--color-red-dim),var(--color-red))">{{ $initials }}</div>
            <div>
                <div class="text-[15px] font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Personal · Full Access</div>
            </div>
            @if ($editing)
                <span class="ml-auto text-[11px] font-semibold tracking-[0.08em] uppercase pb-1.5 border-b text-[#ec5c86] border-red/40">Editing…</span>
            @else
                <button wire:click="startEdit" class="ml-auto text-[11px] font-semibold tracking-[0.08em] uppercase pb-1.5 border-b border-transparent text-text-muted hover:text-white transition-colors">Edit</button>
            @endif
        </div>

        <div class="flex flex-col">
            <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
                <div class="w-[90px] shrink-0 text-[10px] font-bold tracking-[0.16em] uppercase text-text-muted">Name</div>
                <input type="text" wire:model="name" @disabled(! $editing)
                       class="flex-1 bg-transparent border-b py-1 text-[13px] focus:outline-none {{ $editing ? 'border-white/12 focus:border-red' : 'border-transparent opacity-50' }}" />
            </div>
            @error('name') <div class="text-[11px] text-[#ec5c86] mt-1 ml-[106px]">{{ $message }}</div> @enderror

            <div class="flex items-center gap-4 py-3.5 border-b border-white/5">
                <div class="w-[90px] shrink-0 text-[10px] font-bold tracking-[0.16em] uppercase text-text-muted">Email</div>
                <input type="email" wire:model="email" @disabled(! $editing)
                       class="flex-1 bg-transparent border-b py-1 text-[13px] focus:outline-none {{ $editing ? 'border-white/12 focus:border-red' : 'border-transparent opacity-50' }}" />
            </div>
            @error('email') <div class="text-[11px] text-[#ec5c86] mt-1 ml-[106px]">{{ $message }}</div> @enderror

            <div class="flex items-center gap-4 py-3.5">
                <div class="w-[90px] shrink-0 text-[10px] font-bold tracking-[0.16em] uppercase text-text-muted">Password</div>
                <div class="flex-1 relative">
                    <input :type="passVisible ? 'text' : 'password'" wire:model="password" @disabled(! $editing)
                           placeholder="{{ $editing ? 'Leave blank to keep current' : '••••••••••' }}"
                           class="w-full bg-transparent border-b py-1 text-[13px] tracking-[0.2em] focus:outline-none {{ $editing ? 'border-white/12 focus:border-red' : 'border-transparent opacity-50' }}" />
                    <button type="button" @click="passVisible = !passVisible" class="absolute right-0 top-1/2 -translate-y-1/2 text-text-muted hover:text-white text-[13px]">👁</button>
                </div>
            </div>
            @error('password') <div class="text-[11px] text-[#ec5c86] mt-1 ml-[106px]">{{ $message }}</div> @enderror
        </div>

        @if ($editing)
            <div class="flex items-center gap-3.5 pt-4">
                <button wire:click="save" class="text-[11.5px] font-bold tracking-[0.08em] uppercase text-red hover:opacity-75">Save Changes</button>
                <button wire:click="cancelEdit" class="text-[11.5px] text-text-muted hover:text-white tracking-[0.06em] uppercase">Cancel</button>
            </div>
        @endif
    </div>

    {{-- Per-tool settings, discovered from the registry --}}
    @if ($toolsWithSettings->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center gap-3.5 text-[9.5px] font-bold tracking-[0.22em] uppercase text-white/28 mb-5.5 after:content-[''] after:flex-1 after:h-px after:bg-border">Tool Settings</div>

            @foreach ($toolsWithSettings as $tool)
                <a href="{{ $tool->settingsUrl() }}" wire:navigate
                   class="flex items-center gap-4 py-3.5 border-b border-white/5 last:border-b-0 no-underline text-white group">
                    <div class="w-7 text-center text-base opacity-75" style="color: {{ $tool->accent }}">{{ $tool->icon }}</div>
                    <div class="flex-1">
                        <div class="text-[13.5px] font-medium">{{ $tool->name }}</div>
                        <div class="text-[11px] text-text-muted">{{ $tool->tagline }}</div>
                    </div>
                    <span class="shrink-0 text-[13px] text-text-muted opacity-60 group-hover:opacity-100 group-hover:translate-x-[3px] transition-[transform,opacity] duration-200">→</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
