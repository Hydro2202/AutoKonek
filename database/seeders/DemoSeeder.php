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
 * Clearly fictional demonstration data around Lucena City and the rest of Quezon province.
 * Local development only. All demo accounts use the password "password".
 * Run PhilippineLocationSeeder and CategorySeeder first.
 */
class DemoSeeder extends Seeder
{
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

        $cat = fn (string $slug) => Category::where('slug', $slug)->firstOrFail()->id;
        $city = fn (string $name) => City::where('name', $name)->whereHas('province', fn ($q) => $q->where('name', 'Quezon'))->firstOrFail()->id;

        // Each row: owner, category, name, city, barangay, address, description, status, services, latitude, longitude.
        // Coordinates are approximate and only meant for the map demo; the businesses are fictional.
        $rows = [
            [$owner1, 'gas-stations', 'Sample Fuel Stop Alpha', 'Lucena City', 'Ilayang Dupay', 'Maharlika Highway, Quezon Avenue corner', 'A fictional 24-hour fuel station with unleaded, diesel and a small convenience counter.', 'approved', [['Unleaded Gasoline (per liter)', 61.50], ['Diesel (per liter)', 55.20], ['Tire Inflation', null]], 13.9376, 121.6191],
            [$owner1, 'car-wash', 'Demo Sparkle Car Wash', 'Lucena City', 'Ibabang Dupay', 'Diversion Road', 'A fictional hand-wash and detailing shop offering quick exterior and full interior cleaning.', 'approved', [['Exterior Wash', 150], ['Full Interior Vacuum', 250], ['Wax and Polish', 800]], 13.9442, 121.6148],
            [$owner1, 'auto-repair', 'Example Motors Repair Bay', 'Lucena City', 'Gulang-gulang', 'Quezon Avenue', 'A fictional repair shop for oil changes, brake service and engine diagnostics.', 'approved', [['Change Oil', 900], ['Brake Pad Replacement', 1800], ['Computerized Diagnostics', 600]], 13.9331, 121.6269],
            [$owner1, 'tire-and-vulcanizing-shops', 'Fictional Tire Hub', 'Tayabas City', 'Poblacion', 'Maharlika Highway', 'A fictional tire shop offering vulcanizing, balancing and new tire installation.', 'approved', [['Tire Patch', 100], ['Wheel Balancing', 350], ['Tire Installation', 200]], 14.0269, 121.5931],
            [$owner2, 'parking-facilities', 'Demo Covered Parking', 'Lucena City', 'Barra', 'Merchan Street', 'A fictional covered parking building with 24-hour security and hourly rates.', 'approved', [['Hourly Rate', 40], ['Overnight Parking', 250]], 13.9319, 121.6177],
            [$owner2, 'battery-services', 'Sample Power Cell Center', 'Sariaya', 'Poblacion', 'Maharlika Highway', 'A fictional battery shop that tests, charges and replaces car batteries on-site.', 'approved', [['Battery Test', null], ['Battery Charging', 150], ['Battery Replacement (labor)', 200]], 13.9631, 121.5268],
            [$owner2, 'auto-repair', 'Placeholder Auto Clinic', 'Candelaria', 'Masin Sur', 'Maharlika Highway', 'A fictional shop offering aircon repair, tune-ups and general maintenance.', 'approved', [['Aircon Cleaning', 1200], ['Tune-Up', 1500]], 13.9291, 121.4238],
            [$owner2, 'gas-stations', 'Mock Gas and Go', 'Pagbilao', 'Ibabang Palsabangon', 'Maharlika Highway', 'A fictional roadside gas station with a small snack counter.', 'approved', [['Unleaded Gasoline (per liter)', 61.00], ['LPG Refill', 850]], 13.9731, 121.6812],
            [$owner1, 'car-wash', 'Pending Shine Studio', 'Lucena City', 'Isabang', 'Lucena-Tayabas Road', 'A fictional new car wash waiting for administrator approval.', 'pending', [['Basic Wash', 120]], 13.9514, 121.6226],
            [$owner2, 'tire-and-vulcanizing-shops', 'Pending Roll Express', 'Lucban', 'Poblacion', 'Quezon Avenue', 'A fictional tire shop submitted for review.', 'pending', [['Tire Patch', 90]], 14.1136, 121.5562],
            [$owner2, 'parking-facilities', 'Rejected Lot Sample', 'Tiaong', null, 'Unknown', 'Parking lot listing.', 'rejected', [], null, null],
        ];

        $establishments = collect();
        foreach ($rows as [$owner, $slug, $name, $cityName, $barangay, $address, $desc, $status, $services, $lat, $lng]) {
            $e = new Establishment;
            $e->fill([
                'category_id' => $cat($slug), 'name' => $name, 'description' => $desc,
                'address' => $address, 'city_id' => $city($cityName), 'barangay' => $barangay,
                'latitude' => $lat, 'longitude' => $lng,
                'contact_number' => '0917'.rand(1000000, 9999999),
                'opening_hours' => "Mon-Sat: 8:00 AM - 6:00 PM\nSun: 9:00 AM - 3:00 PM",
            ]);
            $e->user_id = $owner->id;
            $e->slug = Establishment::uniqueSlug($name);
            $e->status = $status;
            $e->rejection_reason = $status === 'rejected' ? 'The address is incomplete. Please provide the full street address and a better description, then resubmit.' : null;
            $e->save();

            foreach ($services as [$sName, $price]) {
                $e->services()->create(['name' => $sName, 'price' => $price]);
            }
            $establishments->push($e);
        }

        $comments = [
            5 => ['Fast and friendly service. Highly recommended!', 'Clean place and fair prices. Will come back.'],
            4 => ['Good service overall, just a bit of a wait.', 'Reliable staff and reasonable prices.'],
            3 => ['Average experience. Nothing special but it did the job.'],
            2 => ['Waited too long and the price was higher than listed.'],
        ];

        $approved = $establishments->where('status', 'approved')->values();
        foreach ($approved as $i => $e) {
            foreach ($customers->take(2 + $i % 3) as $j => $customer) {
                $rating = [5, 4, 4, 5, 3, 2][($i + $j) % 6];
                (new Review(['rating' => $rating, 'comment' => collect($comments[$rating])->random()]))
                    ->forceFill(['user_id' => $customer->id, 'establishment_id' => $e->id])
                    ->save();
            }
        }

        // Sample reports: one on a listing, one on a review, one already resolved.
        $reportRows = [
            [$customers[0], $approved[0], null, 'inaccurate_information', 'The posted fuel prices do not match what was charged.', 'pending'],
            [$customers[1], null, Review::first(), 'inappropriate_content', 'This review seems unrelated to the business.', 'pending'],
            [$customers[2], $approved[1], null, 'spam', 'Listing contains advertising text.', 'resolved'],
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
