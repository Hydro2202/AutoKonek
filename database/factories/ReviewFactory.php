<?php

namespace Database\Factories;

use App\Models\Establishment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'establishment_id' => Establishment::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentences(2, true),
        ];
    }
}
