<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Loads real gas stations, car washes, repair shops, tire shops, parking areas and battery shops
 * from all over the Philippines, taken from OpenStreetMap (database/data/ph-places.json).
 *
 * Each place was matched to its city or municipality with the official PSGC boundaries, so it shows up
 * under the right region, province and city. Only facts from the map are stored: a name, a position and,
 * when the map has them, a street, phone number and opening hours. Nothing is invented.
 *
 * The listings are owned by an inactive "unclaimed" account until a real business owner takes over.
 * Safe to run again: places that are already loaded are skipped.
 * Run PhilippineLocationSeeder and CategorySeeder first.
 */
class NationwidePlacesSeeder extends Seeder
{
    private const NOUNS = [
        'gas-stations' => 'Gas station',
        'car-wash' => 'Car wash',
        'auto-repair' => 'Auto repair shop',
        'tire-and-vulcanizing-shops' => 'Tire shop',
        'parking-facilities' => 'Parking area',
        'battery-services' => 'Battery and car parts supplier',
    ];

    private const OSM_TYPES = ['n' => 'node', 'w' => 'way', 'r' => 'relation'];

    public function __construct(private readonly ?string $path = null) {}

    public function run(): void
    {
        $path = $this->path ?? database_path('data/ph-places.json');
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $owner = User::firstOrNew(['email' => 'unclaimed@autokonek.invalid']);
        if (! $owner->exists) {
            $owner->forceFill([
                'name' => 'Unclaimed listings (OpenStreetMap)',
                'password' => Hash::make(Str::random(40)),
                'role' => User::ROLE_OWNER,
                'is_active' => false, // nobody can log in as this account
            ])->save();
        }

        $categoryIds = Category::pluck('id', 'slug');
        $cities = City::with('province:id,name', 'region:id,name')->get()->keyBy('psgc_code');
        $known = DB::table('establishments')->whereNotNull('osm_ref')->pluck('osm_ref')->flip();
        $slugs = DB::table('establishments')->pluck('slug')->flip();
        $now = now();

        $batch = [];
        $flush = function () use (&$batch) {
            if ($batch) {
                DB::table('establishments')->insert($batch);
                $batch = [];
            }
        };

        // Each place: [osm id, name, category index, latitude, longitude, city PSGC code, street, phone, opening hours]
        foreach ($data['places'] as [$osm, $name, $categoryIndex, $latitude, $longitude, $psgc, $street, $phone, $hours]) {
            $osmRef = self::OSM_TYPES[$osm[0]].'/'.substr($osm, 1);
            $city = $cities[$psgc] ?? null;
            $category = $data['categories'][$categoryIndex] ?? null;

            if ($city === null || ! isset($categoryIds[$category]) || isset($known[$osmRef])) {
                continue;
            }

            $slug = Str::limit(Str::slug($name) ?: 'place', 150, '').'-'.substr($osm, 1);
            if (isset($slugs[$slug])) {
                continue;
            }
            $slugs[$slug] = true;
            $known[$osmRef] = true;

            $where = $city->name.', '.$city->areaName();
            $batch[] = [
                'user_id' => $owner->id,
                'category_id' => $categoryIds[$category],
                'city_id' => $city->id,
                'name' => Str::limit($name, 150, ''),
                'slug' => $slug,
                'description' => self::NOUNS[$category].($street ? ' along '.$street : '').' in '.$where
                    .'. Listing details come from OpenStreetMap contributors and have not been claimed by the business yet.',
                'address' => Str::limit($street ?: 'Pinned on the map', 255, ''),
                'barangay' => null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'contact_number' => $phone ? Str::limit($phone, 30, '') : null,
                'contact_email' => null,
                'opening_hours' => $hours,
                'status' => 'approved',
                'rejection_reason' => null,
                'osm_ref' => $osmRef,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                $flush();
            }
        }

        $flush();
    }
}
