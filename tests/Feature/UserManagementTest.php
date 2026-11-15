<?php

namespace Tests\Feature;

use App\Livewire\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_management(): void
    {
        $pm = User::create([
            'nama' => 'PM Satu',
            'email' => 'pm@example.com',
            'password' => 'password',
            'role' => User::ROLE_PM,
            'is_active' => true,
        ]);

        $member = User::create([
            'nama' => 'Anggota Satu',
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $this->actingAs($pm)->get('/users')->assertStatus(403);
        $this->actingAs($member)->get('/users')->assertStatus(403);
    }

    public function test_admin_can_view_create_update_and_deactivate_users(): void
    {
        $admin = User::create([
            'nama' => 'Admin Utama',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/users')
            ->assertStatus(200)
            ->assertSee('Manajemen User');

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openCreate')
            ->set('nama', 'Dewi Lestari')
            ->set('email', 'dewi@example.com')
            ->set('password', 'rahasia123')
            ->set('role', User::ROLE_PM)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('email', 'dewi@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame(User::ROLE_PM, $created->role);
        $this->assertTrue($created->is_active);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleActive', $created->id);

        $this->assertFalse($created->fresh()->is_active);

        // Admin tidak bisa menonaktifkan diri sendiri
        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleActive', $admin->id);

        $this->assertTrue($admin->fresh()->is_active);
    }
}
