<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_establishment(): void
    {
        $admin = User::factory()->admin()->create();
        $e = Establishment::factory()->pending()->create();

        $this->actingAs($admin)->patch(route('admin.establishments.approve', $e))->assertRedirect();

        $this->assertSame('approved', $e->fresh()->status);
    }

    public function test_admin_can_reject_with_reason_and_reason_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        $e = Establishment::factory()->pending()->create();

        $this->actingAs($admin)->patch(route('admin.establishments.reject', $e), ['rejection_reason' => ''])
            ->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending', $e->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.establishments.reject', $e), ['rejection_reason' => 'Address is incomplete, please fix.']);
        $fresh = $e->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Address is incomplete, please fix.', $fresh->rejection_reason);
    }

    public function test_owner_can_see_reason_and_resubmit_a_rejected_listing(): void
    {
        $e = Establishment::factory()->rejected('Needs a better description.')->create();

        $this->actingAs($e->owner)->get(route('owner.establishments.edit', $e))->assertSee('Needs a better description.');

        $this->actingAs($e->owner)->put(route('owner.establishments.update', $e), $this->payload($e, ['description' => str_repeat('Better. ', 10)]))
            ->assertRedirect(route('owner.establishments.index'));

        $fresh = $e->fresh();
        $this->assertSame('pending', $fresh->status);
        $this->assertNull($fresh->rejection_reason);
    }

    public function test_editing_an_approved_listing_requires_reapproval_only_when_details_change(): void
    {
        $e = Establishment::factory()->create();

        // Same values: stays approved.
        $this->actingAs($e->owner)->put(route('owner.establishments.update', $e), $this->payload($e));
        $this->assertSame('approved', $e->fresh()->status);

        // Changed details: goes back to pending.
        $this->actingAs($e->owner)->put(route('owner.establishments.update', $e), $this->payload($e, ['address' => '99 New Road']));
        $this->assertSame('pending', $e->fresh()->status);
    }

    public function test_owner_can_upload_valid_images_and_invalid_ones_are_rejected(): void
    {
        Storage::fake('public');
        $e = Establishment::factory()->create();

        $this->actingAs($e->owner)->post(route('owner.images.store', $e), [
            'images' => [UploadedFile::fake()->image('front.jpg', 600, 400)],
        ])->assertSessionHasNoErrors();

        $image = $e->images()->first();
        $this->assertNotNull($image);
        $this->assertTrue($image->is_primary);
        Storage::disk('public')->assertExists($image->image_path);

        $this->actingAs($e->owner)->post(route('owner.images.store', $e), [
            'images' => [UploadedFile::fake()->create('malware.php', 10, 'application/x-php')],
        ])->assertSessionHasErrors('images.0');
        $this->assertSame(1, $e->images()->count());

        $this->actingAs($e->owner)->post(route('owner.images.store', $e), [
            'images' => [UploadedFile::fake()->image('huge.jpg')->size(5000)],
        ])->assertSessionHasErrors('images.0');

        $this->actingAs($e->owner)->delete(route('owner.images.destroy', [$e, $image]));
        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_invalid_listing_submission_does_not_create_records(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('owner.establishments.store'), ['name' => '', 'category_id' => 9999])
            ->assertSessionHasErrors(['name', 'category_id', 'description', 'address', 'city']);

        $this->assertDatabaseCount('establishments', 0);
    }

    private function payload(Establishment $e, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $e->category_id, 'name' => $e->name, 'description' => $e->description,
            'address' => $e->address, 'city' => $e->city, 'municipality' => $e->municipality,
            'contact_number' => $e->contact_number, 'contact_email' => $e->contact_email, 'opening_hours' => $e->opening_hours,
        ], $overrides);
    }
}
