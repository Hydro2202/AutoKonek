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
            // Test codes start with TST so they can never clash with real PSGC codes.
            'psgc_code' => 'TST'.fake()->unique()->numerify('#######'),
            'name' => ucwords($name),
            'official_name' => ucwords($name),
            'island_group' => fake()->randomElement(['luzon', 'visayas', 'mindanao']),
        ];
    }
}
