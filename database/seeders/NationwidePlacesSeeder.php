<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Real automotive places from OpenStreetMap in every region of the Philippines,
 * about five per category per region: database/data/ph-places.json.
 * Like the Quezon demo places, only facts from the map are used (no invented prices,
 * hours or reviews). Run after DemoSeeder, which creates the two demo owners.
 * Safe to run more than once: places that are already listed are skipped.
 */
class NationwidePlacesSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/ph-places.json');

        if (! file_exists($file)) {
            return;
        }

        $owner1 = User::where('email', 'owner1@autokonek.test')->firstOrFail();
        $owner2 = User::where('email', 'owner2@autokonek.test')->firstOrFail();
        $categoryIds = Category::pluck('id', 'slug');
        $listed = Establishment::whereNotNull('osm_ref')->pluck('osm_ref')->flip();

        foreach (json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR)['places'] as $place) {
            if (isset($listed[$place['osm']])) {
                continue;
            }

            $city = City::where('psgc_code', $place['city_psgc'])->firstOrFail();
            $where = collect([$place['barangay'] ?? null, $city->name])->filter()->implode(', ');
            $along = str_starts_with($place['address'], 'Near ') ? '' : ' along '.$place['address'];

            $establishment = new Establishment([
                'category_id' => $categoryIds[$place['category']],
                'name' => $place['name'],
                'description' => DemoSeeder::NOUNS[$place['category']].$along.' in '.$where
                    .'. Listing details come from OpenStreetMap contributors and have not been claimed by the business yet.',
                'address' => $place['address'],
                'barangay' => $place['barangay'] ?? null,
                'city_id' => $city->id,
                'latitude' => $place['latitude'],
                'longitude' => $place['longitude'],
                'contact_number' => $place['phone'] ?? null,
                'opening_hours' => $place['opening_hours'] ?? null,
            ]);
            $establishment->user_id = in_array($place['category'], ['gas-stations', 'car-wash', 'parking-facilities'], true) ? $owner1->id : $owner2->id;
            $establishment->slug = Establishment::uniqueSlug($place['name']);
            $establishment->status = Establishment::STATUS_APPROVED;
            $establishment->osm_ref = $place['osm'];
            $establishment->save();
        }
    }
}
