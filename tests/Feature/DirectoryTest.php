<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryTest extends TestCase
{
    use RefreshDatabase;

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
}
