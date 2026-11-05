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

    public function test_public_and_customer_pages_render(): void
    {
        $e = Establishment::approved()->first();
        $review = Review::first();

        foreach ([route('home'), route('establishments.index'), route('establishments.show', $e), route('about'), route('contact'), route('places.index'), route('places.index', ['show' => 'all'])] as $url) {
            $this->get($url)->assertOk();
        }

        $customer = User::where('email', 'customer1@autokonek.test')->first();
        foreach ([
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
        $theirs = Establishment::where('user_id', '!=', $owner1->id)->first();
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
}
