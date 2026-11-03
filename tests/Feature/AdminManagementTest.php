<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_deactivate_and_reactivate_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.toggle', $user));
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.users.toggle', $user));
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_self_or_another_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.users.toggle', $admin))->assertSessionHas('error');
        $this->actingAs($admin)->patch(route('admin.users.toggle', $other))->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_non_admin_cannot_toggle_users(): void
    {
        $owner = User::factory()->owner()->create();
        $victim = User::factory()->create();

        $this->actingAs($owner)->patch(route('admin.users.toggle', $victim))->assertForbidden();
        $this->assertTrue($victim->fresh()->is_active);
    }

    public function test_admin_can_create_edit_and_deactivate_categories(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Towing Services', 'description' => 'Tow trucks']);
        $category = Category::where('name', 'Towing Services')->firstOrFail();
        $this->assertSame('towing-services', $category->slug);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'Towing and Recovery']);
        $this->assertSame('towing-and-recovery', $category->fresh()->slug);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['toggle' => 1]);
        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_category_names_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Car Wash']);

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Car Wash'])->assertSessionHasErrors('name');
    }

    public function test_category_in_use_cannot_be_deleted_but_unused_can(): void
    {
        $admin = User::factory()->admin()->create();
        $used = Establishment::factory()->create()->category;
        $unused = Category::factory()->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $used))->assertSessionHas('error');
        $this->assertModelExists($used);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $unused));
        $this->assertModelMissing($unused);
    }

    public function test_dashboard_numbers_come_from_the_database(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->owner()->count(2)->create();
        Establishment::factory()->pending()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $stats = $response->viewData('stats');

        $this->assertSame(3, $stats['pending']);
        $this->assertSame(3, $stats['establishments']);
        $this->assertSame(User::count(), $stats['users']);
        $this->assertSame(User::where('role', 'business_owner')->count(), $stats['owners']);
    }

    public function test_registration_and_login_work(): void
    {
        $this->post(route('register'), [
            'name' => 'Ana Cruz', 'email' => 'ana@example.com', 'account_type' => 'business_owner',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame('business_owner', $user->role);
        $this->assertNotSame('password123', $user->password); // hashed

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'ana@example.com', 'password' => 'password123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_public_pages_load(): void
    {
        $this->get('/')->assertOk()->assertSee('Find the services your vehicle needs');
        foreach (['about', 'contact', 'login', 'register', 'establishments.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
