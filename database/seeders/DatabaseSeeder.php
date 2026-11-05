<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PhilippineLocationSeeder::class,
            CategorySeeder::class,
            AdminSeeder::class,
        ]);

        // Fictional demo data is for local development only, never production.
        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }

        // Real places from all over the Philippines (OpenStreetMap). Safe for production.
        $this->call(NationwidePlacesSeeder::class);
    }
}
