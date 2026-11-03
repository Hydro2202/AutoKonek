@props(['icon' => 'search', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'card px-6 py-12 text-center']) }}>
    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-page text-steel"><x-icon :name="$icon" class="size-6" /></span>
    <h3 class="mt-4 text-base">{{ $title }}</h3>
    @if ($text) <p class="mx-auto mt-1 max-w-md text-sm text-muted">{{ $text }}</p> @endif
    @if (! $slot->isEmpty()) <div class="mt-5">{{ $slot }}</div> @endif
</div>
