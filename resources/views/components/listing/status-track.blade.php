{{-- Where a listing is in the approval process: submitted, in review, live. --}}
@props(['status'])
@php
    $isApproved = $status === 'approved';
    $isRejected = $status === 'rejected';

    $steps = [
        ['label' => 'Submitted', 'state' => 'done', 'tone' => null],
        ['label' => $isRejected ? 'Needs changes' : 'In review', 'state' => $isApproved ? 'done' : 'current', 'tone' => $isRejected ? 'danger' : 'warning'],
        ['label' => 'Live', 'state' => $isApproved ? 'done' : 'todo', 'tone' => null],
    ];
@endphp
<ol {{ $attributes->merge(['class' => 'flex w-full max-w-xs items-start']) }} aria-label="Approval progress">
    @foreach ($steps as $step)
        <li class="track-step" data-state="{{ $step['state'] }}" @if ($step['tone']) data-tone="{{ $step['tone'] }}" @endif @if ($step['state'] === 'current') aria-current="step" @endif>
            <span class="track-dot">
                @if ($step['state'] === 'done') <x-ui.icon name="check" class="size-3 stroke-[3]" />
                @elseif ($step['state'] === 'current' && $step['tone'] === 'danger') <x-ui.icon name="x" class="size-3 stroke-[3]" /> @endif
            </span>
            {{ $step['label'] }}
        </li>
    @endforeach
</ol>
