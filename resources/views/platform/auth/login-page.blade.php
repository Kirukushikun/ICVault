<div class="w-full max-w-[380px]">
    <div class="flex flex-col items-center gap-1 mb-8">
        <div class="font-display text-2xl font-bold tracking-wide">IC<span class="text-red">Vault</span></div>
        <div class="text-[9.5px] tracking-[0.14em] uppercase text-text-muted">Knowledge Quiz</div>
    </div>

    <form wire:submit="login" class="card p-7 flex flex-col gap-4">
        @if ($errors->any())
            <div class="text-[12px] text-[#ec5c86] bg-red/10 border border-red/25 rounded-lg px-3.5 py-2.5">
                {{ $errors->first() }}
            </div>
        @endif

        <div>
            <label for="email" class="block text-[10px] font-bold tracking-[0.16em] uppercase text-text-muted mb-1.5">Email</label>
            <input wire:model="email" id="email" type="email" autofocus autocomplete="username"
                   class="w-full bg-white/3 border-[1.5px] border-border rounded-[10px] px-4 py-3 text-white text-[13px] focus:outline-none focus:border-red" />
        </div>

        <div>
            <label for="password" class="block text-[10px] font-bold tracking-[0.16em] uppercase text-text-muted mb-1.5">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                   class="w-full bg-white/3 border-[1.5px] border-border rounded-[10px] px-4 py-3 text-white text-[13px] focus:outline-none focus:border-red" />
        </div>

        <label class="flex items-center gap-2 text-[12px] text-text-muted cursor-pointer">
            <input wire:model="remember" type="checkbox" class="accent-red" />
            Remember me
        </label>

        <button type="submit"
                class="mt-1 w-full text-white text-xs font-bold tracking-[0.08em] uppercase py-3 rounded-[10px] hover:-translate-y-0.5 transition-transform"
                style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
            Sign In
        </button>
    </form>
</div>
