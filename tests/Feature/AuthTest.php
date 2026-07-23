<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Auth\LoginPage;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_every_protected_route(): void
    {
        foreach (['/', '/quiz', '/import', '/library', '/settings'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_login_page_renders_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSeeLivewire(LoginPage::class);
    }

    public function test_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin_it@bfcgroup.org',
            'password' => 'password',
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create(['email' => 'admin_it@bfcgroup.org', 'password' => 'password']);

        Livewire::test(LoginPage::class)
            ->set('email', 'admin_it@bfcgroup.org')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_reach_every_app_route(): void
    {
        $user = User::factory()->create();

        foreach (['/', '/quiz', '/import', '/library', '/settings'] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
        }
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
