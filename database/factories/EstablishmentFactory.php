<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Establishment>
 */
class EstablishmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' Auto Care';

        return [
            'user_id' => User::factory()->owner(),
            'category_id' => Category::factory(),
            'city_id' => City::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'address' => fake()->streetAddress(),
            'barangay' => null,
            'latitude' => null,
            'longitude' => null,
            'contact_number' => '09'.fake()->numerify('#########'),
            'contact_email' => null,
            'opening_hours' => 'Mon-Sat 8:00 AM - 6:00 PM',
            'status' => Establishment::STATUS_APPROVED,
            'rejection_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => Establishment::STATUS_PENDING]);
    }

    public function rejected(string $reason = 'Please provide a complete address.'): static
    {
        return $this->state(['status' => Establishment::STATUS_REJECTED, 'rejection_reason' => $reason]);
    }

    /** Puts the listing at a GPS position. */
    public function at(float $latitude, float $longitude): static
    {
        return $this->state(['latitude' => $latitude, 'longitude' => $longitude]);
    }
}
