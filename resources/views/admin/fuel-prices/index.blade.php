@extends('layouts.admin')

@section('title', 'Fuel prices')
@section('heading', 'Fuel prices')
@section('subheading', 'Record each weekly DOE price adjustment. The homepage chart updates right away.')

@section('content')
<x-fuel.tracker :series="$series" />

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_23rem]">
    <x-panel.card title="Weeks on record" subtitle="Metro Manila price per liter, with the DOE adjustment that produced it." :flush="true">
        @if ($latestWeek)
            <x-slot:actions>
                <form method="POST" action="{{ route('admin.fuel-prices.destroy-latest') }}"
                      data-confirm="Remove the week of {{ \Illuminate\Support\Carbon::parse($latestWeek)->format('F j, Y') }}? You can add it again with the right numbers.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline btn-sm text-danger-700">Remove latest week</button>
                </form>
            </x-slot:actions>
        @endif
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Week of</th>
                        @foreach (\App\Models\FuelPrice::FUELS as $label)<th>{{ $label }}</th>@endforeach
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weeks as $date => $rows)
                        @php $byFuel = $rows->keyBy('fuel'); @endphp
                        <tr>
                            <td class="font-medium whitespace-nowrap text-navy">{{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}</td>
                            @foreach (array_keys(\App\Models\FuelPrice::FUELS) as $fuel)
                                @php $row = $byFuel[$fuel] ?? null; @endphp
                                <td class="whitespace-nowrap tabular-nums">
                                    @if ($row)
                                        ₱{{ number_format((float) $row->price, 2) }}
                                        @if ($row->change !== null)
                                            <span class="{{ $row->change > 0 ? 'text-danger-700' : ($row->change < 0 ? 'text-success-700' : 'text-muted') }} text-xs font-semibold">
                                                {{ $row->change > 0 ? '▲ +' : ($row->change < 0 ? '▼ −' : '') }}{{ number_format(abs((float) $row->change), 2) }}
                                            </span>
                                        @endif
                                    @else
                                        &ndash;
                                    @endif
                                </td>
                            @endforeach
                            <td class="max-w-xs truncate text-xs text-muted" title="{{ $rows->first()->source }}">{{ $rows->first()->source }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-panel.card>

    <x-panel.card title="Add this week's adjustment" class="h-fit">
        <form method="POST" action="{{ route('admin.fuel-prices.store') }}" class="space-y-4">
            @csrf
            <x-form.input name="effective_on" type="date" label="Takes effect on" required
                          hint="Usually a Tuesday. Must be after the latest week on record." />
            @foreach (\App\Models\FuelPrice::FUELS as $fuel => $label)
                <x-form.input :name="$fuel" type="number" step="0.01" min="-50" max="50" :label="$label.' change (₱ per liter)'" required
                              hint="Use a minus sign for a rollback, for example -1.20." />
            @endforeach
            <x-form.input name="source" label="Source" hint="For example: DOE advisory, Nov 9, 2026" maxlength="255" />
            <button type="submit" class="btn-navy w-full">Add week</button>
        </form>
    </x-panel.card>
</div>
@endsection
