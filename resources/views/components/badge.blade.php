@props(['status'])
@php
    $styles = [
        'pending' => 'bg-amber-50 text-warning border-warning/30',
        'approved' => 'bg-green-50 text-success border-success/30',
        'rejected' => 'bg-red-50 text-danger border-danger/30',
        'resolved' => 'bg-green-50 text-success border-success/30',
        'dismissed' => 'bg-slate-100 text-muted border-line',
        'active' => 'bg-green-50 text-success border-success/30',
        'inactive' => 'bg-slate-100 text-muted border-line',
        'admin' => 'bg-navy text-white border-navy',
        'business_owner' => 'bg-blue-50 text-steel border-steel/30',
        'customer' => 'bg-slate-100 text-ink border-line',
    ];
    $labels = ['business_owner' => 'Business owner'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold '.($styles[$status] ?? $styles['inactive'])]) }}>
    {{ $labels[$status] ?? ucfirst($status) }}
</span>
