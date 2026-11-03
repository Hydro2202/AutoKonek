<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AdminSeeder::class,
        ]);

        // Fictional demo data is for local development only, never production.
        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
