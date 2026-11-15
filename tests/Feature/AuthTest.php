<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $this->get('/login')
            ->assertOk()
            ->assertSee('images/brand/logo-rinjan.png')
            ->assertSee('images/brand/icon.png');
    }

    public function test_active_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role' => 'member',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'nama' => 'Nonaktif',
            'email' => 'nonaktif@example.com',
            'password' => 'rahasia123',
            'role' => 'member',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'nonaktif@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role' => 'member',
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role' => 'member',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
