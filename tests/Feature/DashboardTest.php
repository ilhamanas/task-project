<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Feature;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_projects_by_membership_shows_project_status_cards_and_marks_overdue(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $member = User::create([
            'nama' => 'Anggota',
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        // Project 1: diikuti oleh member, berjalan & terlambat
        $project1 = Project::create([
            'nama' => 'Portal Pelanggan',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
            'tgl_target' => now()->subDays(2)->toDateString(),
        ]);
        $project1->members()->attach($member->id);

        Feature::create(['project_id' => $project1->id, 'nama' => 'F1', 'status' => Feature::STATUS_SELESAI, 'prioritas' => 'tinggi', 'urutan' => 1]);
        Feature::create(['project_id' => $project1->id, 'nama' => 'F2', 'status' => Feature::STATUS_DIKERJAKAN, 'prioritas' => 'sedang', 'urutan' => 2]);
        Feature::create(['project_id' => $project1->id, 'nama' => 'F3', 'status' => Feature::STATUS_GAGAL, 'prioritas' => 'rendah', 'urutan' => 3]);

        // Project 2: TIDAK diikuti member, status planning
        $project2 = Project::create([
            'nama' => 'Sistem Internal Rahasia',
            'status' => Project::STATUS_PLANNING,
            'pic_id' => $admin->id,
            'tgl_target' => now()->addDays(10)->toDateString(),
        ]);
        Feature::create(['project_id' => $project2->id, 'nama' => 'F4', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => 'tinggi', 'urutan' => 1]);

        // Member hanya melihat Project 1 + kartu status project + tanda Terlambat
        Livewire::actingAs($member)
            ->test(Dashboard::class)
            ->assertSee('Portal Pelanggan')
            ->assertSee('Planning')
            ->assertSee('Berjalan')
            ->assertSee('Selesai')
            ->assertSee('Ditunda')
            ->assertDontSee('Sistem Internal Rahasia')
            ->assertSee('Terlambat')
            ->assertViewHas('projectStatusCounts', [
                'planning' => 0,
                'berjalan' => 1,
                'selesai' => 0,
                'ditunda' => 0,
            ])
            ->assertViewHas('overallProgress', 33);

        // Admin melihat kedua project (1 planning, 1 berjalan) & total semua fitur (1 selesai dari 4 = 25%)
        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('Portal Pelanggan')
            ->assertSee('Sistem Internal Rahasia')
            ->assertViewHas('projectStatusCounts', [
                'planning' => 1,
                'berjalan' => 1,
                'selesai' => 0,
                'ditunda' => 0,
            ])
            ->assertViewHas('overallProgress', 25);
    }
}
