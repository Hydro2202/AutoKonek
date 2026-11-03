<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create();

        foreach (['admin.dashboard', 'admin.users.index', 'admin.approvals', 'admin.categories.index', 'admin.reports.index'] as $route) {
            $this->actingAs($customer)->get(route($route))->assertForbidden();
        }
    }

    public function test_business_owners_cannot_access_admin_routes(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customers_cannot_access_owner_pages(): void
    {
        $this->actingAs(User::factory()->create())->get(route('owner.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_owner_cannot_modify_another_owners_establishment(): void
    {
        $owner = User::factory()->owner()->create();
        $other = Establishment::factory()->create(); // belongs to a different owner

        $this->actingAs($owner)->get(route('owner.establishments.edit', $other))->assertForbidden();
        $this->actingAs($owner)->put(route('owner.establishments.update', $other), [
            'category_id' => $other->category_id, 'name' => 'Hacked', 'description' => str_repeat('x', 30),
            'address' => 'x', 'city' => 'x',
        ])->assertForbidden();
        $this->actingAs($owner)->delete(route('owner.establishments.destroy', $other))->assertForbidden();
        $this->actingAs($owner)->post(route('owner.services.store', $other), ['name' => 'Free stuff'])->assertForbidden();

        $this->assertNotSame('Hacked', $other->fresh()->name);
        $this->assertDatabaseCount('services', 0);
    }

    public function test_user_cannot_register_as_admin(): void
    {
        $this->post(route('register'), [
            'name' => 'Sneaky', 'email' => 'sneaky@example.com', 'account_type' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('account_type');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }

    public function test_extra_role_field_is_ignored_on_registration_and_profile_update(): void
    {
        $this->post(route('register'), [
            'name' => 'Normal', 'email' => 'normal@example.com', 'account_type' => 'customer', 'role' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $user = User::where('email', 'normal@example.com')->first();
        $this->assertSame('customer', $user->role);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Normal', 'email' => 'normal@example.com', 'role' => 'admin', 'is_active' => 0,
        ]);
        $this->assertSame('customer', $user->fresh()->role);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_inactive_users_cannot_log_in_or_keep_using_the_site(): void
    {
        $inactive = User::factory()->inactive()->create(['email' => 'off@example.com']);

        $this->post(route('login'), ['email' => 'off@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // A user deactivated mid-session is signed out on the next request.
        $active = User::factory()->owner()->create();
        $this->actingAs($active);
        $active->forceFill(['is_active' => false])->save();

        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_owner_cannot_set_status_via_form(): void
    {
        $owner = User::factory()->owner()->create();
        $category = Category::factory()->create();

        $this->actingAs($owner)->post(route('owner.establishments.store'), [
            'category_id' => $category->id, 'name' => 'Sneaky Shop', 'description' => str_repeat('a', 30),
            'address' => '1 Street', 'city' => 'Cebu City', 'status' => 'approved',
        ])->assertRedirect();

        $this->assertSame('pending', Establishment::where('name', 'Sneaky Shop')->value('status'));
    }
}
