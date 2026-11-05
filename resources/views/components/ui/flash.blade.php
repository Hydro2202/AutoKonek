{{-- Success and error messages appear as toasts in the bottom-right corner. --}}
@php
    $toasts = array_filter([
        'success' => session('success'),
        'error' => session('error'),
    ]);
@endphp

@if ($toasts)
    <div class="pointer-events-none fixed right-4 bottom-4 z-50 flex w-[min(92vw,24rem)] flex-col gap-2">
        @foreach ($toasts as $type => $message)
            <div data-toast="{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}"
                 class="pointer-events-auto relative flex animate-toast-in items-start gap-3 overflow-hidden rounded-md border border-line bg-white py-3 pr-3 pl-4 text-sm shadow-lg shadow-navy/10"
                 style="border-left: 4px solid var(--color-{{ $type === 'error' ? 'danger' : 'success' }})">
                <x-ui.icon :name="$type === 'error' ? 'alert' : 'circle-check'" class="mt-0.5 size-5 shrink-0 {{ $type === 'error' ? 'text-danger-700' : 'text-success-700' }}" />
                <p class="flex-1 text-ink">{{ $message }}</p>
                <button type="button" data-toast-close class="text-muted hover:text-ink" aria-label="Dismiss message"><x-ui.icon name="x" class="size-4" /></button>
                @if ($type === 'success')<span class="toast-timer" aria-hidden="true"></span>@endif
            </div>
        @endforeach
    </div>
@endif
