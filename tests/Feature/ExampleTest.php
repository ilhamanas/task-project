<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_redirects_root_to_dashboard_and_renders_layout(): void
    {
        $user = User::create([
            'nama' => 'Admin Uji',
            'email' => 'admin-test@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/dashboard');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Task Project')
            ->assertSee('Dashboard')
            ->assertSee('images/brand/icon.png')
            ->assertSee('app-sidebar-drawer')
            ->assertSee('id="mobileSidebarLabel"', false)
            ->assertSee('aria-label="Tutup menu"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('table-responsive-cards');
    }

    public function test_application_honors_https_forwarded_headers_behind_reverse_proxy(): void
    {
        $user = User::create([
            'nama' => 'Proxy Uji',
            'email' => 'proxy@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'task.example.com',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/')
            ->assertRedirect('https://task.example.com/dashboard');
    }
}
