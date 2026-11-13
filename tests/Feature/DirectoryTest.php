<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The directory is for signed-in users.
        $this->actingAs(User::factory()->create());
    }

    public function test_unapproved_establishments_are_not_listed_or_viewable(): void
    {
        $approved = Establishment::factory()->create(['name' => 'Visible Garage']);
        $pending = Establishment::factory()->pending()->create(['name' => 'Hidden Pending']);
        $rejected = Establishment::factory()->rejected()->create(['name' => 'Hidden Rejected']);

        $this->get(route('establishments.index'))
            ->assertOk()->assertSee('Visible Garage')
            ->assertDontSee('Hidden Pending')->assertDontSee('Hidden Rejected');

        $this->get(route('establishments.show', $approved))->assertOk();
        $this->get(route('establishments.show', $pending))->assertForbidden();
        $this->get(route('establishments.show', $rejected))->assertForbidden();

        // The owner can still preview their own pending listing.
        $this->actingAs($pending->owner)->get(route('establishments.show', $pending))->assertOk();
    }

    public function test_search_filters_by_name(): void
    {
        Establishment::factory()->create(['name' => 'Alpha Tire Hub']);
        Establishment::factory()->create(['name' => 'Beta Car Wash']);

        $this->get(route('establishments.index', ['q' => 'tire']))
            ->assertSee('Alpha Tire Hub')->assertDontSee('Beta Car Wash');
    }

    public function test_category_and_city_filters(): void
    {
        $wash = Category::factory()->create(['name' => 'Car Wash', 'slug' => 'car-wash']);
        $gas = Category::factory()->create(['name' => 'Gas Stations', 'slug' => 'gas-stations']);

        $lucena = City::factory()->create(['name' => 'Lucena City']);
        $tayabas = City::factory()->create(['name' => 'Tayabas City']);
        Establishment::factory()->create(['name' => 'Sparkle Wash', 'category_id' => $wash->id, 'city_id' => $lucena->id]);
        Establishment::factory()->create(['name' => 'Fuel Corner', 'category_id' => $gas->id, 'city_id' => $tayabas->id]);

        $this->get(route('establishments.index', ['category' => 'car-wash']))
            ->assertSee('Sparkle Wash')->assertDontSee('Fuel Corner');

        $this->get(route('establishments.index', ['city' => $tayabas->id]))
            ->assertSee('Fuel Corner')->assertDontSee('Sparkle Wash');

        $this->get(route('categories.show', $gas))->assertRedirect(route('establishments.index', ['category' => 'gas-stations']));
    }

    public function test_category_counts_follow_the_chosen_place(): void
    {
        $gas = Category::factory()->create(['name' => 'Gas Stations', 'slug' => 'gas-stations']);
        $lucena = City::factory()->create(['name' => 'Lucena City']);
        $tayabas = City::factory()->create(['name' => 'Tayabas City']);
        Establishment::factory()->count(2)->create(['category_id' => $gas->id, 'city_id' => $lucena->id]);
        Establishment::factory()->create(['category_id' => $gas->id, 'city_id' => $tayabas->id]);

        $gasCount = fn (array $query) => $this->get(route('establishments.index', $query))
            ->viewData('categories')->firstWhere('id', $gas->id)->establishments_count;

        $this->assertSame(3, $gasCount([]));
        $this->assertSame(1, $gasCount(['city' => $tayabas->id]));
    }

    public function test_only_customers_get_the_animated_welcome_panel(): void
    {
        Establishment::factory()->count(2)->create();
        $customer = User::factory()->create(['name' => 'Ana Cruz']);

        $this->actingAs($customer)->get(route('establishments.index'))
            ->assertOk()
            ->assertSee('Ana', false)
            ->assertSee('Listings to explore')
            ->assertSee('Your reviews')
            ->assertViewHas('welcome', fn ($welcome) => $welcome['listings'] === 2 && $welcome['myReviews'] === 0);

        foreach ([User::factory()->owner()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get(route('establishments.index'))
                ->assertOk()
                ->assertDontSee('Listings to explore')
                ->assertViewHas('welcome', null);
        }
    }

    public function test_only_business_owners_see_back_to_dashboard_on_the_public_site(): void
    {
        $this->actingAs(User::factory()->owner()->create())->get(route('establishments.index'))
            ->assertOk()->assertSee('Back to dashboard')->assertSee(route('owner.dashboard'));

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get(route('establishments.index'))->assertDontSee('Back to dashboard');
        }
    }

    public function test_list_your_business_banner_is_only_shown_to_guests(): void
    {
        foreach ([User::factory()->create(), User::factory()->owner()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get(route('home'))->assertOk()->assertDontSee('Own a shop, station or lot?');
        }

        auth()->logout();
        $this->get(route('home'))->assertOk()->assertSee('Own a shop, station or lot?');
    }

    public function test_empty_results_show_friendly_message(): void
    {
        $this->get(route('establishments.index', ['q' => 'zzzz-nothing']))
            ->assertOk()->assertSee('No establishments found');
    }

    public function test_directory_is_paginated_and_sorts_by_rating(): void
    {
        Establishment::factory()->count(12)->create();
        $this->get(route('establishments.index'))->assertOk()->assertViewHas('establishments', fn ($p) => $p->count() === 9 && $p->total() === 12);

        $low = Establishment::factory()->create(['name' => 'Low Rated']);
        $high = Establishment::factory()->create(['name' => 'High Rated']);
        Review::factory()->create(['establishment_id' => $low->id, 'rating' => 2]);
        Review::factory()->create(['establishment_id' => $high->id, 'rating' => 5]);

        $first = $this->get(route('establishments.index', ['sort' => 'rating']))->viewData('establishments')->first();
        $this->assertSame('High Rated', $first->name);
    }

    public function test_average_rating_is_calculated_from_reviews(): void
    {
        $e = Establishment::factory()->create();
        Review::factory()->create(['establishment_id' => $e->id, 'rating' => 5]);
        Review::factory()->create(['establishment_id' => $e->id, 'rating' => 3]);

        $fresh = Establishment::withRatings()->find($e->id);
        $this->assertSame(4.0, $fresh->averageRating());
        $this->assertSame(2, $fresh->reviewCount());
    }

    public function test_search_ignores_capital_letters(): void
    {
        Establishment::factory()->create(['name' => 'Alpha Tire Hub']);

        $this->get(route('establishments.index', ['q' => 'TIRE hub']))->assertSee('Alpha Tire Hub');
        $this->get(route('establishments.index', ['q' => 'alpha']))->assertSee('Alpha Tire Hub');
    }

    public function test_search_treats_percent_and_underscore_as_plain_characters(): void
    {
        Establishment::factory()->create(['name' => 'Alpha Garage']);

        $this->get(route('establishments.index', ['q' => '%']))->assertDontSee('Alpha Garage');
        $this->get(route('establishments.index', ['q' => 'A_pha']))->assertDontSee('Alpha Garage');
    }

    public function test_unrated_listings_sort_after_rated_ones(): void
    {
        $unrated = Establishment::factory()->create(['name' => 'Unrated Place']);
        $rated = Establishment::factory()->create(['name' => 'Rated Place']);
        Review::factory()->create(['establishment_id' => $rated->id, 'rating' => 3]);

        $names = $this->get(route('establishments.index', ['sort' => 'rating']))->viewData('establishments')->pluck('name')->all();

        $this->assertSame(['Rated Place', 'Unrated Place'], $names);
    }
}
