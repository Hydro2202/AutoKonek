<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_post_a_review(): void
    {
        $customer = User::factory()->create();
        $e = Establishment::factory()->create();

        $this->actingAs($customer)->post(route('reviews.store', $e), ['rating' => 4, 'comment' => 'Great service overall.'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', ['user_id' => $customer->id, 'establishment_id' => $e->id, 'rating' => 4]);
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $customer = User::factory()->create();
        $e = Establishment::factory()->create();

        foreach ([0, 6, -1, 'abc', 4.5] as $bad) {
            $this->actingAs($customer)->post(route('reviews.store', $e), ['rating' => $bad, 'comment' => 'Valid comment here.'])
                ->assertSessionHasErrors('rating');
        }
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_duplicate_review_is_blocked_but_editing_is_allowed(): void
    {
        $customer = User::factory()->create();
        $e = Establishment::factory()->create();

        $this->actingAs($customer)->post(route('reviews.store', $e), ['rating' => 5, 'comment' => 'First review text.']);
        $this->actingAs($customer)->post(route('reviews.store', $e), ['rating' => 1, 'comment' => 'Second review text.'])
            ->assertSessionHas('error');
        $this->assertSame(1, Review::count());

        $review = Review::first();
        $this->actingAs($customer)->put(route('reviews.update', $review), ['rating' => 3, 'comment' => 'Edited review text.']);
        $this->assertSame(3, $review->fresh()->rating);
    }

    public function test_database_itself_rejects_duplicates_and_bad_ratings(): void
    {
        $review = Review::factory()->create(['rating' => 4]);

        $this->expectException(QueryException::class);
        Review::factory()->create(['user_id' => $review->user_id, 'establishment_id' => $review->establishment_id]);
    }

    public function test_database_check_constraint_blocks_out_of_range_rating(): void
    {
        $this->expectException(QueryException::class);
        Review::factory()->create(['rating' => 9]);
    }

    public function test_cannot_review_unapproved_establishment_or_as_owner(): void
    {
        $customer = User::factory()->create();
        $pending = Establishment::factory()->pending()->create();

        $this->actingAs($customer)->post(route('reviews.store', $pending), ['rating' => 5, 'comment' => 'Trying to sneak in.'])->assertNotFound();

        $approved = Establishment::factory()->create();
        $this->actingAs($approved->owner)->post(route('reviews.store', $approved), ['rating' => 5, 'comment' => 'My own place is great.'])->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customers_cannot_change_other_peoples_reviews(): void
    {
        $review = Review::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->put(route('reviews.update', $review), ['rating' => 1, 'comment' => 'Vandalised text.'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->assertModelExists($review);
    }

    public function test_user_can_report_establishment_and_review(): void
    {
        $reporter = User::factory()->create();
        $e = Establishment::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($reporter)->post(route('reports.store'), [
            'target_type' => 'establishment', 'target_id' => $e->id, 'reason' => 'spam',
        ])->assertRedirect();
        $this->actingAs($reporter)->post(route('reports.store'), [
            'target_type' => 'review', 'target_id' => $review->id, 'reason' => 'inappropriate_content', 'description' => 'Rude.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', ['user_id' => $reporter->id, 'establishment_id' => $e->id, 'review_id' => null, 'status' => 'pending']);
        $this->assertDatabaseHas('reports', ['user_id' => $reporter->id, 'review_id' => $review->id, 'establishment_id' => null]);
    }

    public function test_reports_with_invalid_targets_are_rejected(): void
    {
        $reporter = User::factory()->create();
        $pending = Establishment::factory()->pending()->create();

        foreach ([
            ['target_type' => 'establishment', 'target_id' => 99999, 'reason' => 'spam'],
            ['target_type' => 'review', 'target_id' => 99999, 'reason' => 'spam'],
            ['target_type' => 'establishment', 'target_id' => $pending->id, 'reason' => 'spam'],
            ['target_type' => 'user', 'target_id' => 1, 'reason' => 'spam'],
            ['target_type' => 'establishment', 'reason' => 'spam'],
            ['target_type' => 'establishment', 'target_id' => 'abc', 'reason' => 'spam'],
            ['target_type' => 'establishment', 'target_id' => 1, 'reason' => 'made_up_reason'],
        ] as $data) {
            $this->actingAs($reporter)->post(route('reports.store'), $data)->assertSessionHasErrors();
        }

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_database_requires_exactly_one_report_target(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);
        // Neither target set: blocked by the CHECK constraint.
        DB::table('reports')->insert([
            'user_id' => $user->id, 'reason' => 'spam', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_duplicate_open_reports_are_blocked(): void
    {
        $reporter = User::factory()->create();
        $e = Establishment::factory()->create();
        $data = ['target_type' => 'establishment', 'target_id' => $e->id, 'reason' => 'spam'];

        $this->actingAs($reporter)->post(route('reports.store'), $data);
        $this->actingAs($reporter)->post(route('reports.store'), $data)->assertSessionHas('error');

        $this->assertSame(1, Report::count());
    }

    public function test_owner_cannot_report_their_own_listing(): void
    {
        $e = Establishment::factory()->create();

        $this->actingAs($e->owner)->post(route('reports.store'), ['target_type' => 'establishment', 'target_id' => $e->id, 'reason' => 'spam'])
            ->assertSessionHas('error');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_admin_can_resolve_and_dismiss_reports_and_remove_reviews(): void
    {
        $admin = User::factory()->admin()->create();
        $e = Establishment::factory()->create();
        $review = Review::factory()->create();

        $make = function (array $target) {
            $r = new Report(['reason' => 'spam']);
            $r->user_id = User::factory()->create()->id;
            foreach ($target as $col => $id) {
                $r->{$col} = $id;
            }
            $r->save();

            return $r;
        };
        $one = $make(['establishment_id' => $e->id]);
        $two = $make(['establishment_id' => $e->id]);

        $this->actingAs($admin)->patch(route('admin.reports.update', $one), ['status' => 'resolved'])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.reports.update', $two), ['status' => 'dismissed']);
        $this->assertSame('resolved', $one->fresh()->status);
        $this->assertSame($admin->id, $one->fresh()->resolved_by);
        $this->assertNotNull($one->fresh()->resolved_at);
        $this->assertSame('dismissed', $two->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.reports.update', $one), ['status' => 'bogus'])->assertSessionHasErrors('status');

        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review))->assertRedirect();
        $this->assertModelMissing($review);
    }

    public function test_the_database_allows_only_one_open_report_per_user_and_target(): void
    {
        $reporter = User::factory()->create();
        $e = Establishment::factory()->create();
        $insert = fn (string $status) => DB::table('reports')->insert([
            'user_id' => $reporter->id, 'establishment_id' => $e->id, 'reason' => 'spam',
            'status' => $status, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Closed reports can pile up...
        $insert('resolved');
        $insert('dismissed');
        $insert('resolved');
        // ...but only one report can be open at a time.
        $insert('pending');

        $this->assertSame(4, DB::table('reports')->where('user_id', $reporter->id)->count());

        $this->expectException(QueryException::class);
        $insert('pending');
    }

    public function test_the_database_rejects_a_report_with_two_targets(): void
    {
        $user = User::factory()->create();
        $e = Establishment::factory()->create();
        $review = Review::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('reports')->insert([
            'user_id' => $user->id, 'establishment_id' => $e->id, 'review_id' => $review->id,
            'reason' => 'spam', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
