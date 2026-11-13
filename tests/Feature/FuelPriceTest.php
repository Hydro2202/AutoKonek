<?php

namespace Tests\Feature;

use App\Models\FuelPrice;
use App\Models\User;
use Database\Seeders\FuelPriceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelPriceTest extends TestCase
{
    use RefreshDatabase;

    private function price(string $fuel, string $date): float
    {
        return (float) FuelPrice::where('fuel', $fuel)->where('effective_on', $date)->value('price');
    }

    public function test_seeder_builds_each_week_from_the_doe_anchor_and_adjustments(): void
    {
        $this->seed(FuelPriceSeeder::class);
        $this->seed(FuelPriceSeeder::class); // running it again changes nothing

        $this->assertSame(42, FuelPrice::count());
        // The anchor week is the DOE common price itself.
        $this->assertSame(75.90, $this->price('gasoline', '2026-08-25'));
        // Earlier weeks are worked out backwards, later weeks forwards.
        $this->assertSame(72.33, $this->price('gasoline', '2026-08-11'));
        $this->assertSame(76.18, $this->price('gasoline', '2026-11-10'));
        $this->assertSame(90.70, $this->price('diesel', '2026-11-10'));
        $this->assertSame(123.05, $this->price('kerosene', '2026-11-10'));
        $this->assertSame(-16.80, (float) FuelPrice::where('fuel', 'gasoline')->where('effective_on', '2026-11-10')->value('change'));
        $this->assertNull(FuelPrice::where('fuel', 'gasoline')->where('effective_on', '2026-08-11')->value('change'));
    }

    public function test_homepage_shows_the_fuel_board_with_the_doe_note_and_table(): void
    {
        $this->seed(FuelPriceSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pump prices this week')
            ->assertSee('₱76.18')
            ->assertSee('Rollback')
            ->assertSee('Department of Energy')
            ->assertSee('View all weeks as a table');
    }

    public function test_homepage_works_without_any_fuel_prices(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('Pump prices this week');
    }

    public function test_admin_adds_a_week_and_each_price_continues_from_the_last_one(): void
    {
        $this->seed(FuelPriceSeeder::class);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.fuel-prices.index'))->assertOk()->assertSee('Add this week');

        $this->actingAs($admin)->post(route('admin.fuel-prices.store'), [
            'effective_on' => '2026-11-17', 'gasoline' => '1.25', 'diesel' => '-0.80', 'kerosene' => '0', 'source' => 'DOE advisory, Nov 16, 2026',
        ])->assertSessionHasNoErrors();

        $this->assertSame(77.43, $this->price('gasoline', '2026-11-17'));
        $this->assertSame(89.90, $this->price('diesel', '2026-11-17'));
        $this->assertSame(123.05, $this->price('kerosene', '2026-11-17'));
    }

    public function test_a_week_must_come_after_the_latest_one_and_changes_must_be_sensible(): void
    {
        $this->seed(FuelPriceSeeder::class);
        $admin = User::factory()->admin()->create();
        $valid = ['effective_on' => '2026-11-17', 'gasoline' => '1', 'diesel' => '1', 'kerosene' => '1'];

        $this->actingAs($admin)->post(route('admin.fuel-prices.store'), ['effective_on' => '2026-11-10'] + $valid)->assertSessionHasErrors('effective_on');
        $this->actingAs($admin)->post(route('admin.fuel-prices.store'), ['gasoline' => '75'] + $valid)->assertSessionHasErrors('gasoline');
        $this->actingAs($admin)->post(route('admin.fuel-prices.store'), ['diesel' => 'abc'] + $valid)->assertSessionHasErrors('diesel');

        $this->assertSame(42, FuelPrice::count());
    }

    public function test_admin_can_remove_only_the_latest_week(): void
    {
        $this->seed(FuelPriceSeeder::class);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.fuel-prices.destroy-latest'))->assertSessionHas('success');

        $this->assertSame(39, FuelPrice::count());
        $this->assertSame('2026-11-03', FuelPrice::max('effective_on'));
    }

    public function test_only_administrators_can_manage_fuel_prices(): void
    {
        $this->seed(FuelPriceSeeder::class);

        foreach ([User::factory()->create(), User::factory()->owner()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.fuel-prices.index'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.fuel-prices.store'), [
                'effective_on' => '2026-11-17', 'gasoline' => '1', 'diesel' => '1', 'kerosene' => '1',
            ])->assertForbidden();
        }

        $this->assertSame(42, FuelPrice::count());
    }

    public function test_the_database_rejects_unknown_fuels(): void
    {
        $this->expectException(QueryException::class);
        FuelPrice::create(['fuel' => 'jet-fuel', 'effective_on' => '2026-11-10', 'price' => 50]);
    }

    public function test_the_database_rejects_a_price_of_zero_or_less(): void
    {
        $this->expectException(QueryException::class);
        FuelPrice::create(['fuel' => 'diesel', 'effective_on' => '2026-11-10', 'price' => 0]);
    }
}
