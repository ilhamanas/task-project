<?php

namespace Tests\Feature;

use App\Livewire\Reports\ReportIndex;
use App\Models\Feature;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_by_membership_and_exports_csv_safely(): void
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

        $allowed = Project::create([
            'nama' => 'Project Terbuka',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $member->id,
            'tgl_target' => now()->subDay()->toDateString(),
        ]);
        $allowed->members()->attach($member->id);

        Feature::create(['project_id' => $allowed->id, 'nama' => 'F1', 'status' => Feature::STATUS_SELESAI, 'prioritas' => 'tinggi', 'urutan' => 1]);
        Feature::create(['project_id' => $allowed->id, 'nama' => 'F2', 'status' => Feature::STATUS_GAGAL, 'prioritas' => 'sedang', 'catatan_gagal' => 'Timeout API', 'urutan' => 2]);

        $hidden = Project::create([
            'nama' => 'Project Tertutup',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
        ]);
        Feature::create(['project_id' => $hidden->id, 'nama' => 'F3', 'status' => Feature::STATUS_SELESAI, 'prioritas' => 'sedang', 'urutan' => 1]);

        // Member hanya melihat Project Terbuka dan tidak bisa memaksa filterProject ke Project Tertutup
        Livewire::actingAs($member)
            ->test(ReportIndex::class)
            ->assertSee('Project Terbuka')
            ->assertDontSee('Project Tertutup')
            ->assertSee('Timeout API')
            ->assertViewHas('rataProgress', 50)
            ->set('filterProject', (string) $hidden->id)
            ->assertDontSee('Project Tertutup')
            ->call('resetFilters')
            ->call('exportCsv')
            ->assertFileDownloaded('laporan-project-' . now()->format('Y-m-d') . '.csv');

        // Admin melihat kedua project
        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->assertSee('Project Terbuka')
            ->assertSee('Project Tertutup')
            ->assertViewHas('totalProjects', 2)
            ->assertViewHas('rataProgress', 67);
    }
}
