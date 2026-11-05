<?php

namespace App\Services;

use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Support\Collection;

/**
 * Supplies the region > province > city options for the cascading selectors.
 * Regions without provinces (Metro Manila) list their cities directly.
 */
class LocationLookup
{
    /** @return Collection<int, Region> */
    public function regions(): Collection
    {
        return Region::orderBy('id')->get();
    }

    /** @return Collection<int, Province> */
    public function provinces(?int $regionId): Collection
    {
        return $regionId ? Province::where('region_id', $regionId)->orderBy('name')->get() : collect();
    }

    /** @return Collection<int, City> */
    public function cities(?int $regionId, ?int $provinceId): Collection
    {
        if ($provinceId) {
            return City::where('province_id', $provinceId)->orderBy('name')->get();
        }

        // Only regions that have no provinces list their cities directly.
        if ($regionId && ! Province::where('region_id', $regionId)->exists()) {
            return City::where('region_id', $regionId)->orderBy('name')->get();
        }

        return collect();
    }

    /** The city a new listing starts with (Lucena City, Quezon, from config/autokonek.php). */
    public function defaultCityId(): ?int
    {
        return City::where('name', config('autokonek.home.city'))
            ->whereHas('province', fn ($q) => $q->where('name', config('autokonek.home.province')))
            ->value('id');
    }

    /**
     * Options for a saved or submitted choice, so a form can show the right lists on first load.
     *
     * @return array{regions: Collection<int, Region>, provinces: Collection<int, Province>, cities: Collection<int, City>, regionId: ?int, provinceId: ?int, cityId: ?int}
     */
    public function forSelection(?int $regionId, ?int $provinceId, ?int $cityId): array
    {
        // A city decides its own province and region.
        if ($cityId && ($city = City::find($cityId))) {
            $regionId = $city->region_id;
            $provinceId = $city->province_id;
        } elseif ($provinceId && ($province = Province::find($provinceId))) {
            $regionId = $province->region_id;
        }

        return [
            'regions' => $this->regions(),
            'provinces' => $this->provinces($regionId),
            'cities' => $this->cities($regionId, $provinceId),
            'regionId' => $regionId,
            'provinceId' => $provinceId,
            'cityId' => $cityId,
        ];
    }
}
