@props(['light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 font-extrabold tracking-tight text-xl']) }}>
    <span class="flex size-8 items-center justify-center rounded-md bg-accent text-navy"><x-icon name="car" class="size-5" /></span>
    <span class="{{ $light ? 'text-white' : 'text-navy' }}">Auto<span class="text-accent">Konek</span></span>
</span>
