<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'psgc_code' => fake()->unique()->numerify('##0000000'),
            'name' => ucwords($name),
            'official_name' => ucwords($name),
            'island_group' => fake()->randomElement(['luzon', 'visayas', 'mindanao']),
        ];
    }
}
