<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalAndConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_public_and_show_their_titles(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Privacy Policy')->assertSee('Data Privacy Act');
        $this->get(route('legal.terms'))->assertOk()->assertSee('Terms of Service')->assertSee('No payments or refunds');
        $this->get(route('legal.cookies'))->assertOk()->assertSee('Cookie Policy')->assertSee(config('autokonek.consent_cookie'));
    }

    public function test_cookie_policy_lists_the_real_session_cookie_name(): void
    {
        $this->get(route('legal.cookies'))->assertSee(config('session.cookie'));
    }

    public function test_every_public_page_links_to_the_legal_pages_and_has_the_cookie_banner(): void
    {
        foreach (['home', 'login', 'register', 'legal.privacy', 'legal.terms', 'legal.cookies'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee(route('legal.privacy'), false)
                ->assertSee(route('legal.terms'), false)
                ->assertSee(route('legal.cookies'), false)
                ->assertSee('id="cookie-banner"', false);
        }
    }

    public function test_registration_requires_accepting_the_terms(): void
    {
        $data = [
            'name' => 'No Terms', 'email' => 'noterms@example.com', 'account_type' => 'customer',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ];

        $this->post(route('register'), $data)->assertSessionHasErrors('terms');
        $this->assertDatabaseMissing('users', ['email' => 'noterms@example.com']);

        $this->post(route('register'), $data + ['terms' => '1'])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'noterms@example.com']);
    }

    public function test_signed_in_users_do_not_get_a_second_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get(route('login'))->assertRedirect(route('dashboard'));
    }
}
