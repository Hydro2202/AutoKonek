<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFuelPriceRequest;
use App\Models\FuelPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Administrators record each weekly DOE price adjustment here. The new price of each
 * fuel is its latest price plus the adjustment, so the chart always continues the line.
 */
class FuelPriceController extends Controller
{
    public function index(): View
    {
        $weeks = FuelPrice::orderByDesc('effective_on')->get()
            ->groupBy(fn (FuelPrice $row) => $row->effective_on->toDateString());

        return view('admin.fuel-prices.index', [
            'series' => FuelPrice::chartSeries(),
            'weeks' => $weeks,
            'latestWeek' => $weeks->keys()->first(),
        ]);
    }

    public function store(StoreFuelPriceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $missing = collect(FuelPrice::FUELS)->keys()
            ->reject(fn (string $fuel) => FuelPrice::where('fuel', $fuel)->exists());

        if ($missing->isNotEmpty()) {
            return back()->withInput()->withErrors(['effective_on' => 'There is no earlier price to adjust. Load the starting prices with "php artisan db:seed --class=FuelPriceSeeder".']);
        }

        DB::transaction(function () use ($data) {
            foreach (array_keys(FuelPrice::FUELS) as $fuel) {
                $previous = FuelPrice::where('fuel', $fuel)->orderByDesc('effective_on')->first();

                FuelPrice::create([
                    'fuel' => $fuel,
                    'effective_on' => $data['effective_on'],
                    'price' => round((float) $previous->price + (float) $data[$fuel], 2),
                    'change' => $data[$fuel],
                    'source' => $data['source'] ?: 'DOE advisory',
                ]);
            }
        });

        return back()->with('success', 'Prices for the week of '.date('F j, Y', strtotime($data['effective_on'])).' were added.');
    }

    /** Remove the latest week only, so a mistake can be corrected without breaking the line. */
    public function destroyLatest(): RedirectResponse
    {
        $latest = FuelPrice::max('effective_on');

        if ($latest === null || FuelPrice::distinct()->count('effective_on') < 2) {
            return back()->with('error', 'The first week on record cannot be removed.');
        }

        FuelPrice::where('effective_on', $latest)->delete();

        return back()->with('success', 'The latest week was removed.');
    }
}
