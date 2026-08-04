@props(['model' => null])

<label {{ $attributes->except('wire:model.live')->class(['relative inline-block w-10 h-6 shrink-0 cursor-pointer']) }}>
    <input type="checkbox" {{ $attributes->only('wire:model.live')->merge($model ? ['x-model' => $model] : []) }} class="peer opacity-0 w-0 h-0 absolute" />
    <span class="absolute inset-0 rounded-full bg-white/9 border border-white/7 transition-colors
                 peer-checked:bg-red peer-checked:border-red/40
                 before:content-[''] before:absolute before:w-4 before:h-4 before:rounded-full before:left-[3px] before:top-[3px] before:bg-white/45 before:shadow before:transition-transform
                 peer-checked:before:translate-x-4 peer-checked:before:bg-white"></span>
</label>
