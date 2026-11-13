<?php

namespace Database\Seeders;

use App\Models\FuelPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Loads the real weekly fuel price history from database/data/fuel-prices.json.
 *
 * The file holds one known DOE price (the "anchor" week) and the DOE weekly adjustments
 * around it. Prices before the anchor are worked out backwards, later ones forwards.
 * The week before the earliest adjustment becomes the first point, with no change.
 * Safe to run more than once.
 */
class FuelPriceSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/fuel-prices.json')), true, 512, JSON_THROW_ON_ERROR);

        $anchorDate = $data['anchor']['effective_on'];
        $adjustments = collect($data['adjustments'])->sortBy('effective_on')->keyBy('effective_on');

        foreach (array_keys(FuelPrice::FUELS) as $fuel) {
            $anchorPrice = $data['anchor']['prices'][$fuel];

            /** @var array<string, array{price: float, change: float|null, source: string}> $weeks */
            $weeks = [$anchorDate => [
                'price' => $anchorPrice,
                'change' => $adjustments[$anchorDate][$fuel] ?? null,
                'source' => $data['anchor']['source'],
            ]];

            // Forwards: each later week is the previous price plus that week's adjustment.
            $price = $anchorPrice;
            foreach ($adjustments->filter(fn ($a, $date) => $date > $anchorDate) as $date => $adjustment) {
                $price = round($price + $adjustment[$fuel], 2);
                $weeks[$date] = ['price' => $price, 'change' => $adjustment[$fuel], 'source' => $adjustment['source']];
            }

            // Backwards: the week before an adjustment had that week's price minus the adjustment.
            $price = $anchorPrice;
            foreach ($adjustments->filter(fn ($a, $date) => $date <= $anchorDate)->reverse() as $date => $adjustment) {
                $price = round($price - $adjustment[$fuel], 2);
                $previousWeek = Carbon::parse($date)->subWeek()->toDateString();
                $earlier = $adjustments->get($previousWeek);

                $weeks[$previousWeek] = [
                    'price' => $price,
                    'change' => $earlier[$fuel] ?? null,
                    'source' => $earlier['source'] ?? 'Worked out from the DOE adjustment of the following week',
                ];
            }

            foreach ($weeks as $date => $week) {
                FuelPrice::updateOrCreate(
                    ['fuel' => $fuel, 'effective_on' => $date],
                    ['price' => $week['price'], 'change' => $week['change'], 'source' => $week['source']],
                );
            }
        }
    }
}
