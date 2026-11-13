<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** One fuel's Metro Manila pump price for the week that starts on "effective_on". */
class FuelPrice extends Model
{
    /** @var array<string, string> Fuel key => label shown to people. */
    public const FUELS = [
        'gasoline' => 'Gasoline (RON 91)',
        'diesel' => 'Diesel',
        'kerosene' => 'Kerosene',
    ];

    protected $fillable = ['fuel', 'effective_on', 'price', 'change', 'source'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'change' => 'decimal:2',
        ];
    }

    /** Stored as a plain date ("2026-11-10") so lookups by date match exactly. */
    protected function effectiveOn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Carbon::parse($value)->startOfDay(),
            set: fn (mixed $value) => Carbon::parse($value)->toDateString(),
        );
    }

    /**
     * Every week on record, oldest first, grouped by fuel. Ready for the price chart.
     *
     * @return array<string, array{label: string, points: list<array{date: string, price: float, change: float|null}>}>
     */
    public static function chartSeries(): array
    {
        $rows = static::orderBy('effective_on')->get()->groupBy('fuel');

        return collect(self::FUELS)
            ->map(fn (string $label, string $fuel) => [
                'label' => $label,
                'points' => ($rows[$fuel] ?? new Collection)->map(fn (self $row) => [
                    'date' => $row->effective_on->toDateString(),
                    'price' => (float) $row->price,
                    'change' => $row->change === null ? null : (float) $row->change,
                ])->values()->all(),
            ])
            ->all();
    }
}
