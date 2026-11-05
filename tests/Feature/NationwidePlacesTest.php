<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\NationwidePlacesSeeder;
use Database\Seeders\PhilippineLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NationwidePlacesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PhilippineLocationSeeder::class, CategorySeeder::class]);
        (new NationwidePlacesSeeder(base_path('tests/Fixtures/ph-places-sample.json')))->run();
    }

    public function test_real_places_from_all_over_the_country_are_loaded_under_the_right_city(): void
    {
        // Two of the five sample rows are skipped: an unknown city and an unknown category.
        $this->assertSame(3, Establishment::count());

        $davao = Establishment::where('name', 'Sample Fuel Davao')->firstOrFail();
        $this->assertSame('Davao City', $davao->city->name);
        $this->assertSame('Davao Del Sur', $davao->city->province->name);
        $this->assertSame('gas-stations', $davao->category->slug);
        $this->assertSame('node/1001', $davao->osm_ref);
        $this->assertSame('approved', $davao->status);
        $this->assertSame('J.P. Laurel Avenue', $davao->address);
        $this->assertSame('+63 82 000 0000', $davao->contact_number);
        $this->assertSame('24/7', $davao->opening_hours);
        $this->assertEquals(7.0731, (float) $davao->latitude);

        $cebu = Establishment::where('name', 'Sample Wash Cebu')->firstOrFail();
        $this->assertSame('way/1002', $cebu->osm_ref);
        $this->assertSame('Pinned on the map', $cebu->address);
        $this->assertNull($cebu->contact_number);

        // Metro Manila places have no province: the label falls back to Metro Manila.
        $manila = Establishment::where('name', 'Sample Garage Manila')->firstOrFail();
        $this->assertSame('Manila City, Metro Manila', $manila->city->fullName());
    }

    public function test_the_places_belong_to_an_inactive_unclaimed_account(): void
    {
        $owner = User::where('email', 'unclaimed@autokonek.invalid')->firstOrFail();

        $this->assertFalse($owner->is_active);
        $this->assertTrue($owner->isOwner());
        $this->assertSame(3, $owner->establishments()->count());
        // Nobody can sign in as it.
        $this->assertFalse(auth()->guard('web')->validate(['email' => $owner->email, 'password' => 'password', 'is_active' => true]));
    }

    public function test_running_the_seeder_again_does_not_duplicate_anything(): void
    {
        (new NationwidePlacesSeeder(base_path('tests/Fixtures/ph-places-sample.json')))->run();

        $this->assertSame(3, Establishment::count());
        $this->assertSame(1, User::where('email', 'unclaimed@autokonek.invalid')->count());
    }

    public function test_visitors_can_browse_gas_stations_across_the_philippines(): void
    {
        $this->actingAs(User::factory()->create());
        $gas = Category::where('slug', 'gas-stations')->first();
        $davao = City::where('name', 'Davao City')->first();

        // No place chosen: stations from anywhere in the country.
        $this->get(route('establishments.index', ['category' => $gas->slug]))->assertOk()->assertSee('Sample Fuel Davao');

        // Narrowing down by region, province and city.
        $this->get(route('establishments.index', ['region' => $davao->region_id]))->assertSee('Sample Fuel Davao');
        $this->get(route('establishments.index', ['province' => $davao->province_id]))->assertSee('Sample Fuel Davao');
        $this->get(route('establishments.index', ['city' => $davao->id]))->assertSee('Sample Fuel Davao')->assertDontSee('Sample Wash Cebu');
        $this->get(route('establishments.index', ['category' => 'car-wash']))->assertSee('Sample Wash Cebu')->assertDontSee('Sample Fuel Davao');
    }

    public function test_the_homepage_search_covers_the_whole_country_by_region(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('home'))->assertOk()->assertSee('All of the Philippines')->assertSee('name="region"', false);
    }
}
