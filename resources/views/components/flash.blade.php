{{-- Session feedback (success / error) and a summary when validation fails. --}}
@php
    $success = session('success');
    $error = session('error');
@endphp

<div class="space-y-3">
    @if ($success)
        <div data-flash="success" role="status" class="flex items-start gap-3 rounded-md border border-success/30 bg-green-50 px-4 py-3 text-sm text-green-900">
            <x-icon name="check" class="size-5 shrink-0 text-success" />
            <p class="flex-1">{{ $success }}</p>
            <button type="button" data-flash-close class="text-green-900/60 hover:text-green-900" aria-label="Dismiss"><x-icon name="x" class="size-4" /></button>
        </div>
    @endif

    @if ($error)
        <div data-flash="error" role="alert" class="flex items-start gap-3 rounded-md border border-danger/30 bg-red-50 px-4 py-3 text-sm text-red-900">
            <x-icon name="alert" class="size-5 shrink-0 text-danger" />
            <p class="flex-1">{{ $error }}</p>
            <button type="button" data-flash-close class="text-red-900/60 hover:text-red-900" aria-label="Dismiss"><x-icon name="x" class="size-4" /></button>
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-md border border-danger/30 bg-red-50 px-4 py-3 text-sm text-red-900">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
