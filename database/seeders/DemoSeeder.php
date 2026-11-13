<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demonstration data for Lucena City and nearby Quezon towns. Local development only.
 * All demo accounts use the password "password".
 * Run PhilippineLocationSeeder and CategorySeeder first.
 *
 *  - The approved listings are REAL places (gas stations, car washes, repair shops...) taken from
 *    OpenStreetMap: database/data/quezon-places.json. Only facts from the map are used; no prices,
 *    opening hours or reviews are invented for them.
 *  - "AutoKonek Sample Garage" is the one clearly fictional approved listing. It exists so that
 *    services with prices, reviews and reports can be demonstrated without making up opinions
 *    about real businesses.
 *  - The pending and rejected listings are fictional and show the approval workflow.
 */
class DemoSeeder extends Seeder
{
    /** What each category's real places are called in the generated description. */
    public const NOUNS = [
        'gas-stations' => 'Gas station',
        'car-wash' => 'Car wash',
        'auto-repair' => 'Auto repair shop',
        'tire-and-vulcanizing-shops' => 'Tire shop',
        'parking-facilities' => 'Parking area',
        'battery-services' => 'Battery and car parts supplier',
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        $makeUser = function (string $name, string $email, string $role) use ($password) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name' => $name, 'password' => $password, 'role' => $role,
                'is_active' => true, 'email_verified_at' => now(),
            ])->save();

            return $user;
        };

        $owner1 = $makeUser('Maria Santos', 'owner1@autokonek.test', User::ROLE_OWNER);
        $owner2 = $makeUser('Jose Reyes', 'owner2@autokonek.test', User::ROLE_OWNER);
        $customers = collect([
            ['Ana Cruz', 'customer1@autokonek.test'],
            ['Ben Garcia', 'customer2@autokonek.test'],
            ['Carla Mendoza', 'customer3@autokonek.test'],
            ['Dan Villanueva', 'customer4@autokonek.test'],
        ])->map(fn ($c) => $makeUser($c[0], $c[1], User::ROLE_CUSTOMER));

        $categoryId = fn (string $slug) => Category::where('slug', $slug)->firstOrFail()->id;
        $cityId = fn (string $name) => City::where('name', $name)->whereHas('province', fn ($q) => $q->where('name', 'Quezon'))->firstOrFail()->id;

        $make = function (User $owner, array $data, string $status, ?string $osmRef = null, ?string $reason = null) use ($categoryId, $cityId) {
            $e = new Establishment;
            $e->fill([
                'category_id' => $categoryId($data['category']),
                'name' => $data['name'],
                'description' => $data['description'],
                'address' => $data['address'],
                'barangay' => $data['barangay'] ?? null,
                'city_id' => $cityId($data['city']),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'contact_number' => $data['phone'] ?? null,
                'opening_hours' => $data['opening_hours'] ?? null,
            ]);
            $e->user_id = $owner->id;
            $e->slug = Establishment::uniqueSlug($data['name']);
            $e->status = $status;
            $e->rejection_reason = $reason;
            $e->osm_ref = $osmRef;
            $e->save();

            return $e;
        };

        // 1) Real places from OpenStreetMap. Gas, car wash and parking go to the first owner, the rest to the second.
        //    (Places in the other regions are added by NationwidePlacesSeeder.)
        $places = json_decode(file_get_contents(database_path('data/quezon-places.json')), true, 512, JSON_THROW_ON_ERROR)['places'];

        foreach ($places as $place) {
            $owner = in_array($place['category'], ['gas-stations', 'car-wash', 'parking-facilities'], true) ? $owner1 : $owner2;

            $where = collect([$place['barangay'] ?? null, $place['city']])->filter()->implode(', ');
            $place['description'] = self::NOUNS[$place['category']].' along '.$place['address'].' in '.$where
                .'. Listing details come from OpenStreetMap contributors and have not been claimed by the business yet.';

            $make($owner, $place, Establishment::STATUS_APPROVED, $place['osm']);
        }

        // 2) The fictional sample: services with prices, reviews and reports.
        $sample = $make($owner2, [
            'category' => 'auto-repair', 'name' => 'AutoKonek Sample Garage', 'city' => 'Lucena City', 'barangay' => 'Gulang-gulang',
            'address' => 'Sample Street (not a real address)',
            'description' => 'A fictional garage used to demonstrate services, prices, reviews and reports. It is not a real business.',
            'latitude' => 13.9331, 'longitude' => 121.6269,
            'phone' => '0917 000 0000', 'opening_hours' => "Mon-Sat: 8:00 AM - 6:00 PM\nSun: closed",
        ], Establishment::STATUS_APPROVED);

        foreach ([['Change Oil', 900], ['Brake Pad Replacement', 1800], ['Computerized Diagnostics', 600], ['Wheel Alignment', null]] as [$name, $price]) {
            $sample->services()->create(['name' => $name, 'price' => $price]);
        }

        // 3) Fictional listings that show the approval workflow in the admin area.
        $make($owner1, [
            'category' => 'car-wash', 'name' => 'Pending Shine Studio', 'city' => 'Lucena City', 'barangay' => 'Isabang',
            'address' => 'Lucena-Tayabas Road', 'latitude' => 13.9514, 'longitude' => 121.6226,
            'description' => 'A fictional new car wash waiting for administrator approval.',
        ], Establishment::STATUS_PENDING)->services()->create(['name' => 'Basic Wash', 'price' => 120]);

        $make($owner2, [
            'category' => 'tire-and-vulcanizing-shops', 'name' => 'Pending Roll Express', 'city' => 'Lucban',
            'address' => 'Quezon Avenue', 'description' => 'A fictional tire shop submitted for review.',
        ], Establishment::STATUS_PENDING)->services()->create(['name' => 'Tire Patch', 'price' => 90]);

        $make($owner2, [
            'category' => 'parking-facilities', 'name' => 'Rejected Lot Sample', 'city' => 'Tiaong',
            'address' => 'Unknown', 'description' => 'Parking lot listing.',
        ], Establishment::STATUS_REJECTED, null, 'The address is incomplete. Please provide the full street address and a better description, then resubmit.');

        // 4) Sample reviews, only on the fictional garage.
        $comments = [
            [5, 'Fast and friendly service. Highly recommended!'],
            [4, 'Good service overall, just a bit of a wait.'],
            [3, 'Average experience. Nothing special but it did the job.'],
            [5, 'Clear prices and they explained the work before starting.'],
        ];
        $reviews = collect();
        foreach ($customers as $i => $customer) {
            [$rating, $comment] = $comments[$i];
            $reviews->push((new Review(['rating' => $rating, 'comment' => $comment]))
                ->forceFill(['user_id' => $customer->id, 'establishment_id' => $sample->id]));
            $reviews->last()->save();
        }

        // 5) Sample reports: one on the garage, one on a review, one already resolved.
        $reportRows = [
            [$customers[0], $sample, null, 'inaccurate_information', 'The opening hours do not match what was posted.', 'pending'],
            [$customers[1], null, $reviews[2], 'inappropriate_content', 'This review seems unrelated to the business.', 'pending'],
            [$customers[2], $sample, null, 'spam', 'Listing contains advertising text.', 'resolved'],
        ];
        foreach ($reportRows as [$reporter, $est, $review, $reason, $desc, $status]) {
            $r = new Report(['reason' => $reason, 'description' => $desc]);
            $r->user_id = $reporter->id;
            $r->establishment_id = $est?->id;
            $r->review_id = $review?->id;
            $r->status = $status;
            if ($status === 'resolved') {
                $r->resolved_by = User::where('role', User::ROLE_ADMIN)->value('id');
                $r->resolved_at = now();
            }
            $r->save();
        }
    }
}
