{{-- One metric on the admin dashboard. "note" is always computed from real data by the controller. --}}
@props(['label', 'value', 'note' => null, 'icon', 'href' => null, 'alert' => false])
<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'dash-tile dash-rise group block rounded-md border bg-white p-4 '.($alert ? 'border-accent' : 'border-line').($href ? ' hover:border-navy' : '')]) }}>
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-muted">{{ $label }}</span>
        <span class="flex size-8 items-center justify-center rounded-[5px] {{ $alert ? 'bg-accent-100 text-accent-600' : 'bg-steel-50 text-steel' }}"><x-ui.icon :name="$icon" class="size-4" /></span>
    </div>
    <p class="mt-3 text-3xl leading-none font-semibold tracking-tight text-navy tabular-nums" data-count="{{ $value }}">{{ number_format($value) }}</p>
    @if ($note)<p class="delta mt-2">{{ $note }}</p>@endif
</{{ $href ? 'a' : 'div' }}>
