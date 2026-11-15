<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_route_returns_custom_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertStatus(404)
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Kembali ke Dashboard')
            ->assertSee('404.svg')
            ->assertSee('images/brand/icon.png');
    }

    public function test_unauthorized_project_returns_custom_403_page(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $member = User::create([
            'nama' => 'Member',
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Tertutup',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
        ]);

        $this->actingAs($member)
            ->get('/projects/' . $project->id)
            ->assertStatus(403)
            ->assertSee('Akses Ditolak')
            ->assertSee('Kembali ke Dashboard');
    }
}
