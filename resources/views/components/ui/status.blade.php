{{-- Status chip with a colored dot. Works for listings, reports, users and roles. --}}
@props(['status'])
@php
    $styles = [
        'pending' => ['border-warning/30 bg-warning-50 text-warning-700', 'bg-warning', 'Pending'],
        'approved' => ['border-success/30 bg-success-50 text-success-700', 'bg-success', 'Approved'],
        'rejected' => ['border-danger/30 bg-danger-50 text-danger-700', 'bg-danger', 'Rejected'],
        'resolved' => ['border-success/30 bg-success-50 text-success-700', 'bg-success', 'Resolved'],
        'dismissed' => ['border-line-strong bg-page text-muted', 'bg-muted', 'Dismissed'],
        'active' => ['border-success/30 bg-success-50 text-success-700', 'bg-success', 'Active'],
        'inactive' => ['border-line-strong bg-page text-muted', 'bg-muted', 'Inactive'],
        'admin' => ['border-navy bg-navy text-white', 'bg-accent', 'Administrator'],
        'business_owner' => ['border-steel/30 bg-steel-50 text-steel', 'bg-steel', 'Business owner'],
        'customer' => ['border-line-strong bg-page text-ink', 'bg-muted', 'Customer'],
    ];
    [$classes, $dot, $label] = $styles[$status] ?? $styles['inactive'];
@endphp
<span {{ $attributes->merge(['class' => 'chip '.$classes]) }}>
    <span class="size-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
</span>
