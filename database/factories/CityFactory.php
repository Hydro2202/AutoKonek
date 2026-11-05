<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        return [
            // The region is taken from the province so the two always agree.
            'province_id' => Province::factory(),
            'region_id' => fn (array $attributes) => Province::find($attributes['province_id'])->region_id,
            'psgc_code' => 'TST'.fake()->unique()->numerify('#######'),
            'name' => ucwords(fake()->unique()->words(2, true)).' City',
            'is_city' => true,
        ];
    }

    /** A place with no province, like the cities of Metro Manila. */
    public function withoutProvince(): static
    {
        return $this->state(fn () => ['province_id' => null, 'region_id' => Region::factory()]);
    }
}
