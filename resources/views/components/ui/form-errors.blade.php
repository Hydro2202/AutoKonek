{{-- Summary shown at the top of a page when a form failed validation. --}}
@if ($errors->any())
    <div role="alert" class="mb-5 rounded-md border border-danger/30 bg-danger-50 px-4 py-3 text-sm text-danger-700">
        <p class="font-semibold">Fix these before continuing:</p>
        <ul class="mt-1 list-disc space-y-0.5 pl-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
