@props(['light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="sign size-9 {{ $light ? '!bg-accent !text-navy-950' : '' }}" @if ($light) style="box-shadow: inset 0 0 0 2px var(--color-accent), inset 0 0 0 3px rgb(20 43 69 / 0.35)" @endif>
        <x-ui.icon name="car" class="size-5" />
    </span>
    <span class="font-display text-2xl leading-none font-bold tracking-wide {{ $light ? 'text-white' : 'text-navy' }}">Auto<span class="text-accent">Konek</span></span>
</span>
