<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use App\Support\MapLinks;
use Database\Seeders\PhilippineLocationSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationAndMapTest extends TestCase
{
    use RefreshDatabase;

    // Approximate positions used by the demo data.
    private const LUCENA = [13.9376, 121.6191];

    private const TAYABAS = [14.0269, 121.5931];

    private const SARIAYA = [13.9631, 121.5268];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PhilippineLocationSeeder::class);
    }

    private function city(string $name, string $province = 'Quezon'): City
    {
        return City::where('name', $name)->whereHas('province', fn ($q) => $q->where('name', $province))->firstOrFail();
    }

    private function listingIn(City $city, string $name, ?array $position = null): Establishment
    {
        $listing = Establishment::factory()->create(['name' => $name, 'city_id' => $city->id]);

        if ($position) {
            $listing->forceFill(['latitude' => $position[0], 'longitude' => $position[1]])->save();
        }

        return $listing;
    }

    public function test_the_whole_philippine_hierarchy_is_loaded(): void
    {
        $this->assertSame(17, Region::count());
        $this->assertGreaterThanOrEqual(80, Province::count());
        $this->assertGreaterThan(1600, City::count());
        $this->assertGreaterThan(140, City::where('is_city', true)->count());

        $lucena = $this->city('Lucena City');
        $this->assertTrue($lucena->is_city);
        $this->assertSame('Quezon', $lucena->province->name);
        $this->assertSame('CALABARZON', $lucena->region->name);
        $this->assertSame('Lucena City, Quezon', $lucena->fullName());

        // Metro Manila cities have no province and are labelled by the metro area.
        $manila = City::where('name', 'Manila City')->firstOrFail();
        $this->assertNull($manila->province_id);
        $this->assertSame('Metro Manila', $manila->areaName());

        $this->assertTrue(City::where('name', 'Las Piñas City')->exists());
    }

    public function test_seeding_the_locations_twice_does_not_duplicate_them(): void
    {
        $before = City::count();
        $this->seed(PhilippineLocationSeeder::class);

        $this->assertSame($before, City::count());
    }

    public function test_cascading_endpoints_return_provinces_and_cities(): void
    {
        $calabarzon = Region::where('name', 'CALABARZON')->firstOrFail();
        $quezon = Province::where('name', 'Quezon')->where('region_id', $calabarzon->id)->firstOrFail();

        $this->getJson(route('places.provinces', ['region' => $calabarzon->id]))
            ->assertOk()->assertJsonFragment(['name' => 'Quezon'])->assertJsonFragment(['name' => 'Batangas']);

        $this->getJson(route('places.cities', ['province' => $quezon->id]))
            ->assertOk()->assertJsonFragment(['name' => 'Lucena City'])->assertJsonFragment(['name' => 'Sariaya']);
    }

    public function test_regions_without_provinces_list_their_cities_directly(): void
    {
        $ncr = Region::where('name', 'NCR')->firstOrFail();

        $this->getJson(route('places.provinces', ['region' => $ncr->id]))->assertOk()->assertExactJson([]);
        $this->getJson(route('places.cities', ['region' => $ncr->id]))->assertOk()->assertJsonFragment(['name' => 'Makati City']);

        // A region that has provinces does not dump all its towns without a province.
        $calabarzon = Region::where('name', 'CALABARZON')->firstOrFail();
        $this->getJson(route('places.cities', ['region' => $calabarzon->id]))->assertOk()->assertExactJson([]);
    }

    public function test_the_directory_filters_by_region_province_and_city(): void
    {
        $lucena = $this->city('Lucena City');
        $this->listingIn($lucena, 'Lucena Garage');
        $this->listingIn($this->city('Sariaya'), 'Sariaya Garage');
        $this->listingIn(City::where('name', 'Cebu City')->firstOrFail(), 'Cebu Garage');

        $byCity = $this->get(route('establishments.index', ['city' => $lucena->id]));
        $byCity->assertSee('Lucena Garage')->assertDontSee('Sariaya Garage')->assertDontSee('Cebu Garage');

        $byProvince = $this->get(route('establishments.index', ['province' => $lucena->province_id]));
        $byProvince->assertSee('Lucena Garage')->assertSee('Sariaya Garage')->assertDontSee('Cebu Garage');

        $byRegion = $this->get(route('establishments.index', ['region' => $lucena->region_id]));
        $byRegion->assertSee('Lucena Garage')->assertSee('Sariaya Garage')->assertDontSee('Cebu Garage');

        $this->get(route('establishments.index'))->assertSee('Cebu Garage');
    }

    public function test_the_places_page_lists_regions_and_cities_with_counts(): void
    {
        $this->listingIn($this->city('Lucena City'), 'Lucena Garage');

        $this->get(route('places.index'))
            ->assertOk()
            ->assertSee('Region IV-A (CALABARZON)')
            ->assertSee('National Capital Region (NCR)')
            ->assertSee('Lucena City')
            ->assertSee('Davao City')
            ->assertDontSee('Adams'); // a municipality: only shown in the "all" view

        $this->get(route('places.index', ['show' => 'all']))->assertOk()->assertSee('Adams')->assertSee('Sariaya');
    }

    public function test_near_me_sorts_by_distance_and_puts_listings_without_a_pin_last(): void
    {
        $this->listingIn($this->city('Sariaya'), 'Second Garage', self::SARIAYA);
        $this->listingIn($this->city('Lucena City'), 'Near Garage', self::LUCENA);
        $this->listingIn($this->city('Tayabas City'), 'Third Garage', self::TAYABAS);
        $this->listingIn($this->city('Lucena City'), 'No Pin Garage');

        // A visitor standing near the center of Lucena City: Sariaya is a little closer than Tayabas.
        $response = $this->get(route('establishments.index', ['lat' => 13.9373, 'lng' => 121.6170, 'sort' => 'nearest']))->assertOk();

        $names = $response->viewData('establishments')->pluck('name')->all();
        $this->assertSame(['Near Garage', 'Second Garage', 'Third Garage', 'No Pin Garage'], $names);

        $near = $response->viewData('establishments')->first();
        $this->assertEqualsWithDelta(0.2, $near->distance_km, 0.3);
        $this->assertNull($response->viewData('establishments')->last()->distance_km);
        $response->assertSee('from you');
    }

    public function test_near_me_needs_a_valid_pair_of_coordinates(): void
    {
        $this->get(route('establishments.index', ['lat' => 999, 'lng' => 121]))->assertSessionHasErrors('lat');
        $this->get(route('establishments.index', ['lat' => 13.9]))->assertSessionHasErrors('lng');

        // Asking for "nearest" without a position falls back to the normal list instead of failing.
        $this->listingIn($this->city('Lucena City'), 'Any Garage');
        $this->get(route('establishments.index', ['sort' => 'nearest']))->assertOk()->assertSee('Any Garage');
    }

    public function test_the_listing_page_offers_directions_and_a_click_to_load_map(): void
    {
        $listing = $this->listingIn($this->city('Lucena City'), 'Pinned Garage', self::LUCENA);
        $listing->update(['barangay' => 'Ilayang Dupay']);

        $this->get(route('establishments.show', $listing))
            ->assertOk()
            ->assertSee('Getting there')
            ->assertSee('Brgy. Ilayang Dupay, Lucena City, Quezon')
            ->assertSee('google.com/maps/dir/', false)
            ->assertSee('destination=13.937600%2C121.619100', false)
            ->assertSee('waze.com/ul', false)
            ->assertSee('data-map-load', false)
            // No third-party map is contacted until the visitor asks for it.
            ->assertDontSee('<iframe', false);
    }

    public function test_directions_fall_back_to_the_address_when_there_is_no_pin(): void
    {
        $listing = $this->listingIn($this->city('Lucena City'), 'Unpinned Garage');
        $listing->update(['address' => 'Quezon Avenue']);

        $this->get(route('establishments.show', $listing))
            ->assertOk()
            ->assertSee('no GPS pin yet')
            ->assertSee('Unpinned+Garage%2C+Quezon+Avenue%2C+Lucena+City%2C+Quezon%2C+Philippines', false)
            ->assertDontSee('data-map-load', false);
    }

    public function test_map_links_are_built_from_coordinates_or_the_address(): void
    {
        $pinned = $this->listingIn($this->city('Lucena City'), 'Pinned', self::LUCENA);
        $links = MapLinks::for($pinned);

        $this->assertTrue($links->hasCoordinates());
        $this->assertStringContainsString('travelmode=driving', $links->googleDirections());
        $this->assertStringContainsString('ll=13.937600%2C121.619100', $links->waze());
        $this->assertStringContainsString('mlat=13.937600', $links->openStreetMap());
        $this->assertStringContainsString('openstreetmap.org/export/embed.html', $links->embedUrl());

        $unpinned = $this->listingIn($this->city('Sariaya'), 'Unpinned');
        $this->assertNull(MapLinks::for($unpinned)->embedUrl());
        $this->assertStringContainsString('openstreetmap.org/search', MapLinks::for($unpinned)->openStreetMap());
    }

    public function test_an_owner_can_save_a_listing_with_a_place_and_a_gps_pin(): void
    {
        $owner = User::factory()->owner()->create();
        $category = Category::factory()->create();
        $lucena = $this->city('Lucena City');

        $this->actingAs($owner)->post(route('owner.establishments.store'), [
            'category_id' => $category->id, 'name' => 'Pinned Shop', 'description' => str_repeat('a', 30),
            'city_id' => $lucena->id, 'barangay' => 'Gulang-gulang', 'address' => 'Quezon Avenue',
            'latitude' => '13.931400', 'longitude' => '121.626900',
        ])->assertRedirect();

        $saved = Establishment::where('name', 'Pinned Shop')->firstOrFail();
        $this->assertSame($lucena->id, $saved->city_id);
        $this->assertSame('Gulang-gulang', $saved->barangay);
        $this->assertSame('13.931400', $saved->latitude);
        $this->assertSame('pending', $saved->status);
    }

    public function test_the_gps_pin_and_place_are_validated(): void
    {
        $owner = User::factory()->owner()->create();
        $category = Category::factory()->create();
        $base = [
            'category_id' => $category->id, 'name' => 'Bad Shop', 'description' => str_repeat('a', 30),
            'city_id' => $this->city('Lucena City')->id, 'address' => 'Somewhere',
        ];

        $this->actingAs($owner)->post(route('owner.establishments.store'), $base + ['latitude' => '95', 'longitude' => '121'])->assertSessionHasErrors('latitude');
        $this->actingAs($owner)->post(route('owner.establishments.store'), $base + ['latitude' => '13.9'])->assertSessionHasErrors('longitude');
        $this->actingAs($owner)->post(route('owner.establishments.store'), $base + ['longitude' => 'abc', 'latitude' => '13.9'])->assertSessionHasErrors('longitude');
        $this->actingAs($owner)->post(route('owner.establishments.store'), array_merge($base, ['city_id' => 99999999]))->assertSessionHasErrors('city_id');
        $this->actingAs($owner)->post(route('owner.establishments.store'), array_diff_key($base, ['city_id' => 1]))->assertSessionHasErrors('city_id');

        $this->assertDatabaseCount('establishments', 0);
    }

    public function test_the_database_rejects_half_a_coordinate_pair(): void
    {
        $listing = Establishment::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('establishments')->where('id', $listing->id)->update(['latitude' => 13.9, 'longitude' => null]);
    }

    public function test_a_place_in_use_cannot_be_deleted(): void
    {
        $city = $this->city('Lucena City');
        $this->listingIn($city, 'Anchor Garage');

        $this->expectException(QueryException::class);
        $city->delete();
    }

    public function test_the_new_listing_form_starts_in_the_home_city(): void
    {
        $owner = User::factory()->owner()->create();
        $lucena = $this->city('Lucena City');

        $response = $this->actingAs($owner)->get(route('owner.establishments.create'))->assertOk();

        $this->assertSame($lucena->id, $response->viewData('location')['cityId']);
        $response->assertSee('Region IV-A (CALABARZON)')->assertSee('data-location-picker', false);
    }

    public function test_the_homepage_suggests_places_that_have_listings(): void
    {
        $this->listingIn($this->city('Lucena City'), 'Lucena Garage');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Around Lucena City and Quezon')
            ->assertSee(route('places.index'), false);
    }
}
