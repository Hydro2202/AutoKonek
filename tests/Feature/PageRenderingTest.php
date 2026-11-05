<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\PhilippineLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Loads every page for every role with realistic data, to catch template errors. */
class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PhilippineLocationSeeder::class, CategorySeeder::class, DemoSeeder::class]);
    }

    public function test_guest_and_customer_pages_render(): void
    {
        $e = Establishment::approved()->first();
        $review = Review::first();

        // Guests can open the landing page and the legal pages.
        foreach ([route('home'), route('legal.privacy'), route('legal.terms'), route('legal.cookies'), route('login'), route('register')] as $url) {
            $this->get($url)->assertOk();
        }

        // Everything else needs an account.
        $customer = User::where('email', 'customer1@autokonek.test')->first();
        foreach ([
            route('home'), route('establishments.index'), route('about'), route('contact'),
            route('places.index'), route('places.index', ['show' => 'all']),
            route('establishments.show', $e), route('customer.reviews'), route('customer.reports'), route('profile.edit'),
            route('reports.create', ['type' => 'establishment', 'id' => $e->id]),
            route('reports.create', ['type' => 'review', 'id' => $review->id]),
        ] as $url) {
            $this->actingAs($customer)->get($url)->assertOk();
        }
    }

    public function test_owner_pages_render(): void
    {
        $owner = User::where('email', 'owner1@autokonek.test')->first();
        $e = $owner->establishments()->first();

        foreach ([
            route('owner.dashboard'), route('owner.establishments.index'), route('owner.establishments.create'),
            route('owner.establishments.edit', $e), route('owner.services.index', $e), route('owner.images.index', $e),
            route('owner.reviews'), route('profile.edit'),
        ] as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }

        // Add, update and delete a service end to end.
        $this->actingAs($owner)->post(route('owner.services.store', $e), ['name' => 'Test Service', 'price' => '123.50'])->assertRedirect();
        $service = Service::where('name', 'Test Service')->first();
        $this->assertSame('123.50', (string) $service->price);
        $this->actingAs($owner)->put(route('owner.services.update', [$e, $service]), ['name' => 'Renamed', 'price' => ''])->assertRedirect();
        $this->assertNull($service->fresh()->price);
        $this->actingAs($owner)->delete(route('owner.services.destroy', [$e, $service]))->assertRedirect();
        $this->assertModelMissing($service);
    }

    public function test_owner_cannot_touch_another_owners_service(): void
    {
        $owner1 = User::where('email', 'owner1@autokonek.test')->first();
        $mine = $owner1->establishments()->first();
        $theirs = Establishment::where('user_id', '!=', $owner1->id)->has('services')->first();
        $theirService = $theirs->services()->first();

        // Mixing a service from another listing under my own listing's URL must fail.
        $this->actingAs($owner1)->delete(route('owner.services.destroy', [$mine, $theirService]))->assertNotFound();
        $this->actingAs($owner1)->delete(route('owner.services.destroy', [$theirs, $theirService]))->assertForbidden();
        $this->assertModelExists($theirService);
    }

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $e = Establishment::where('status', 'pending')->first();
        $category = $e->category;

        foreach ([
            route('admin.dashboard'), route('admin.users.index'), route('admin.users.index', ['q' => 'ana', 'role' => 'customer']),
            route('admin.approvals'), route('admin.establishments.index'), route('admin.establishments.index', ['status' => 'approved', 'q' => 'car']),
            route('admin.establishments.show', $e), route('admin.categories.index'), route('admin.categories.show', $category),
            route('admin.categories.edit', $category), route('admin.reviews.index'), route('admin.reviews.index', ['rating' => 5, 'q' => 'good']),
            route('admin.reports.index'), route('admin.reports.index', ['status' => 'pending', 'type' => 'review']),
            route('admin.messages.index'), route('profile.edit'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_demo_data_matches_the_brief(): void
    {
        $this->assertSame(6, Category::count());
        $this->assertSame(2, User::where('role', 'business_owner')->count());
        $this->assertGreaterThanOrEqual(3, User::where('role', 'customer')->count());
        $this->assertGreaterThan(0, Establishment::where('status', 'pending')->count());
        $this->assertGreaterThan(0, Establishment::where('status', 'rejected')->count());
        $this->assertGreaterThan(0, Report::count());
    }

    public function test_the_demo_listings_are_real_places_from_openstreetmap(): void
    {
        $real = Establishment::whereNotNull('osm_ref')->get();

        $this->assertGreaterThanOrEqual(20, $real->count());
        $this->assertSame(['approved'], $real->pluck('status')->unique()->all());

        // Real gas stations around Lucena City, with a street and a GPS pin.
        $gas = $real->filter(fn ($e) => $e->category->slug === 'gas-stations');
        $this->assertGreaterThanOrEqual(6, $gas->count());
        foreach (['Petron', 'Shell', 'Caltex'] as $brand) {
            $this->assertTrue($gas->contains('name', $brand), "$brand is missing");
        }
        $this->assertTrue($real->every(fn ($e) => $e->hasCoordinates() && filled($e->address)));

        // Nothing is invented for real businesses: no reviews, prices or opening hours unless the map has them.
        $this->assertSame(0, Review::whereIn('establishment_id', $real->pluck('id'))->count());
        $this->assertSame(0, Service::whereIn('establishment_id', $real->pluck('id'))->count());

        // Every category has at least one real place.
        $this->assertSame(6, $real->pluck('category_id')->unique()->count());
    }

    public function test_real_listings_credit_openstreetmap_and_the_sample_garage_is_labelled_fictional(): void
    {
        $this->actingAs(User::where('email', 'customer1@autokonek.test')->first());

        $petron = Establishment::whereNotNull('osm_ref')->where('name', 'Petron')->first();
        $this->get(route('establishments.show', $petron))
            ->assertOk()
            ->assertSee('OpenStreetMap contributors')
            ->assertSee('has not claimed it yet');

        $sample = Establishment::where('name', 'AutoKonek Sample Garage')->first();
        $this->assertNull($sample->osm_ref);
        $this->get(route('establishments.show', $sample))->assertOk()->assertSee('not a real business')->assertDontSee('has not claimed it yet');
    }

    public function test_the_footer_shows_only_the_website_name(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('&copy; '.date('Y').' AutoKonek', false)->assertDontSee('college project')->assertDontSee('fictional');
    }
}
