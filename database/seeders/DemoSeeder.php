<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Clearly fictional demonstration data. Local development only.
 * All demo accounts use the password "password".
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

        // [owner, category slug, name, city, municipality, address, description, status, services]
        $rows = [
            [$owner1, 'gas-stations', 'Sample Fuel Stop Alpha', 'Cebu City', 'Mabolo', '12 Demo Highway', 'A fictional 24-hour fuel station with unleaded, diesel and a small convenience counter.', 'approved', [['Unleaded Gasoline (per liter)', 61.50], ['Diesel (per liter)', 55.20], ['Tire Inflation', null]]],
            [$owner1, 'car-wash', 'Demo Sparkle Car Wash', 'Cebu City', 'Lahug', '45 Sample Street', 'A fictional hand-wash and detailing shop offering quick exterior and full interior cleaning.', 'approved', [['Exterior Wash', 150], ['Full Interior Vacuum', 250], ['Wax and Polish', 800]]],
            [$owner1, 'auto-repair', 'Example Motors Repair Bay', 'Mandaue City', 'Subangdako', '8 Placeholder Avenue', 'A fictional repair shop for oil changes, brake service and engine diagnostics.', 'approved', [['Change Oil', 900], ['Brake Pad Replacement', 1800], ['Computerized Diagnostics', 600]]],
            [$owner1, 'tire-and-vulcanizing-shops', 'Fictional Tire Hub', 'Lapu-Lapu City', 'Pajo', '21 Mock Road', 'A fictional tire shop offering vulcanizing, balancing and new tire installation.', 'approved', [['Tire Patch', 100], ['Wheel Balancing', 350], ['Tire Installation', 200]]],
            [$owner2, 'parking-facilities', 'Demo Covered Parking', 'Cebu City', 'Capitol Site', '3 Imaginary Plaza', 'A fictional covered parking building with 24-hour security and hourly rates.', 'approved', [['Hourly Rate', 40], ['Overnight Parking', 250]]],
            [$owner2, 'battery-services', 'Sample Power Cell Center', 'Talisay City', 'Bulacao', '17 Test Lane', 'A fictional battery shop that tests, charges and replaces car batteries on-site.', 'approved', [['Battery Test', null], ['Battery Charging', 150], ['Battery Replacement (labor)', 200]]],
            [$owner2, 'auto-repair', 'Placeholder Auto Clinic', 'Mandaue City', 'Centro', '90 Sample Boulevard', 'A fictional shop offering aircon repair, tune-ups and general maintenance.', 'approved', [['Aircon Cleaning', 1200], ['Tune-Up', 1500]]],
            [$owner2, 'gas-stations', 'Mock Gas and Go', 'Lapu-Lapu City', 'Basak', '5 Fiction Street', 'A fictional roadside gas station with a small snack counter.', 'approved', [['Unleaded Gasoline (per liter)', 61.00], ['LPG Refill', 850]]],
            [$owner1, 'car-wash', 'Pending Shine Studio', 'Cebu City', 'Banilad', '77 Draft Street', 'A fictional new car wash waiting for administrator approval.', 'pending', [['Basic Wash', 120]]],
            [$owner2, 'tire-and-vulcanizing-shops', 'Pending Roll Express', 'Talisay City', 'Poblacion', '14 Queue Road', 'A fictional tire shop submitted for review.', 'pending', [['Tire Patch', 90]]],
            [$owner2, 'parking-facilities', 'Rejected Lot Sample', 'Cebu City', null, 'Unknown', 'Parking lot listing.', 'rejected', []],
        ];

        $establishments = collect();
        foreach ($rows as [$owner, $slug, $name, $city, $muni, $address, $desc, $status, $services]) {
            $e = new Establishment;
            $e->fill([
                'category_id' => $cat($slug), 'name' => $name, 'description' => $desc,
                'address' => $address, 'city' => $city, 'municipality' => $muni,
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
