@props(['light' => false])
{{-- On hover or keyboard focus the car revs, drives out of its badge and pulls back in (see .brand-logo in motion.css). --}}
<span {{ $attributes->merge(['class' => 'brand-logo inline-flex items-center gap-2.5']) }}>
    <span class="brand-badge sign size-9 {{ $light ? '!bg-accent !text-navy-950' : '' }}" @if ($light) style="box-shadow: inset 0 0 0 2px var(--color-accent), inset 0 0 0 3px rgb(20 43 69 / 0.35)" @endif>
        <span class="brand-road" aria-hidden="true"></span>
        <x-ui.icon name="car" class="brand-car size-5" />
    </span>
    <span class="font-display text-2xl leading-none font-bold tracking-wide {{ $light ? 'text-white' : 'text-navy' }}">Auto<span class="brand-konek inline-block text-accent">Konek</span></span>
</span>
