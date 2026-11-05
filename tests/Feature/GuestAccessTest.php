<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Visitors who are not logged in can only see the landing page, the legal pages, login and register. */
class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login_from_every_members_only_page(): void
    {
        $listing = Establishment::factory()->create();

        foreach ([
            route('establishments.index'),
            route('establishments.show', $listing),
            route('categories.show', $listing->category),
            route('places.index'),
            route('about'),
            route('contact'),
            route('profile.edit'),
            route('customer.reviews'),
            route('reports.create', ['type' => 'establishment', 'id' => $listing->id]),
            route('owner.dashboard'),
            route('admin.dashboard'),
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_the_login_page_explains_why_the_guest_was_sent_there(): void
    {
        $this->get(route('establishments.index'))->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk()->assertSee('Please log in or create an account to continue.');
    }

    public function test_guests_cannot_do_anything_either(): void
    {
        $listing = Establishment::factory()->create();

        $this->post(route('reviews.store', $listing), ['rating' => 5, 'comment' => 'Great place, honestly.'])->assertRedirect(route('login'));
        $this->post(route('reports.store'), ['target_type' => 'establishment', 'target_id' => $listing->id, 'reason' => 'spam'])->assertRedirect(route('login'));
        $this->post(route('contact.store'), ['name' => 'A', 'email' => 'a@example.com', 'subject' => 'Hi', 'message' => 'Hello there'])->assertRedirect(route('login'));
        $this->post(route('owner.establishments.store'), ['name' => 'X'])->assertRedirect(route('login'));
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_the_place_lists_are_not_available_to_guests(): void
    {
        $this->getJson(route('places.provinces', ['region' => 1]))->assertUnauthorized();
        $this->getJson(route('places.cities', ['region' => 1]))->assertUnauthorized();
    }

    public function test_guests_can_open_the_landing_page_legal_pages_and_auth_pages(): void
    {
        foreach (['home', 'legal.privacy', 'legal.terms', 'legal.cookies', 'login', 'register'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_the_landing_page_hides_the_directory_from_guests(): void
    {
        Establishment::factory()->create(['name' => 'Secret Garage']);

        $guest = $this->get(route('home'))->assertOk();
        $guest->assertDontSee('Secret Garage')
            ->assertDontSee('Recently approved')
            ->assertSee('Log in to start')
            ->assertSee('Create free account');
        // The members-only navigation is not shown either.
        $guest->assertDontSee(route('establishments.index'), false)->assertDontSee(route('places.index'), false);

        $this->actingAs(User::factory()->create())->get(route('home'))
            ->assertSee('Secret Garage')->assertSee('Recently approved')->assertSee(route('establishments.index'), false);
    }

    public function test_after_logging_in_the_visitor_lands_on_the_page_they_asked_for(): void
    {
        $user = User::factory()->create(['email' => 'driver@example.com']);
        $listing = Establishment::factory()->create();

        $this->get(route('establishments.show', $listing))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => 'driver@example.com', 'password' => 'password'])
            ->assertRedirect(route('establishments.show', $listing));

        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_accounts_lose_access_to_members_only_pages(): void
    {
        $this->actingAs(User::factory()->inactive()->create())
            ->get(route('establishments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_the_car_transition_is_only_on_the_essential_links(): void
    {
        Establishment::factory()->create(['name' => 'Gas Stop']);
        $this->actingAs(User::factory()->create());

        // Listing cards opt in to the transition...
        $this->get(route('establishments.index'))->assertSee('data-transition', false)->assertSee('data-rt-label="Gas Stop"', false);

        // ...but ordinary pages have no opted-in links.
        $this->get(route('about'))->assertDontSee('data-transition', false);
        $this->get(route('profile.edit'))->assertDontSee('data-transition', false);
    }
}
