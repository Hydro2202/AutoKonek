<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Gas Stations' => 'Fuel stations for gasoline, diesel and LPG.',
            'Car Wash' => 'Exterior washing, interior cleaning and detailing.',
            'Auto Repair' => 'General repair, maintenance and diagnostics.',
            'Tire and Vulcanizing Shops' => 'Tire sales, patching, balancing and alignment.',
            'Parking Facilities' => 'Open and covered parking spaces.',
            'Battery Services' => 'Battery testing, replacement and charging.',
        ];

        foreach ($categories as $name => $description) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true],
            );
        }
    }
}
