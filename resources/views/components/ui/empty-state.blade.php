@props(['icon' => 'search', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-line-strong bg-white px-6 py-12 text-center']) }}>
    <span class="sign mx-auto size-12"><x-ui.icon :name="$icon" class="size-6" /></span>
    <h3 class="mt-4 text-xl">{{ $title }}</h3>
    @if ($text) <p class="mx-auto mt-1.5 max-w-md text-[0.9375rem] text-muted">{{ $text }}</p> @endif
    @if (! $slot->isEmpty()) <div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div> @endif
</div>
