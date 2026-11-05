<?php

namespace Database\Factories;

use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Province>
 */
class ProvinceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'psgc_code' => fake()->unique()->numerify('##00#0000'),
            'name' => ucwords(fake()->unique()->words(2, true)),
        ];
    }
}
