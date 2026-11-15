<?php

namespace Tests\Feature;

use App\Livewire\Projects\ProjectShow;
use App\Models\Feature;
use App\Models\FeatureLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BacklogAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_backlog_shows_belum_mulai_ordered_by_priority_can_add_and_start_feature(): void
    {
        $member = User::create([
            'nama' => 'Bima',
            'email' => 'bima@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Backlog',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $member->id,
        ]);
        $project->members()->attach($member->id);

        $low = Feature::create(['project_id' => $project->id, 'nama' => 'Fitur Rendah', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => Feature::PRIORITY_RENDAH, 'urutan' => 1]);
        $high = Feature::create(['project_id' => $project->id, 'nama' => 'Fitur Tinggi', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => Feature::PRIORITY_TINGGI, 'urutan' => 2]);
        $mid = Feature::create(['project_id' => $project->id, 'nama' => 'Fitur Sedang', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => Feature::PRIORITY_SEDANG, 'urutan' => 3]);
        $started = Feature::create(['project_id' => $project->id, 'nama' => 'Sudah Dikerjakan', 'status' => Feature::STATUS_DIKERJAKAN, 'prioritas' => Feature::PRIORITY_TINGGI, 'urutan' => 1]);

        $component = Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('setViewMode', 'backlog')
            ->assertSee('Fitur Tinggi')
            ->assertDontSee('Sudah Dikerjakan')
            ->assertViewHas('backlogFeatures', function ($items) use ($high, $mid, $low) {
                return $items->pluck('id')->all() === [$high->id, $mid->id, $low->id];
            });

        // Tambah fitur langsung dari backlog
        $component
            ->call('openCreateFeature')
            ->set('nama', 'Fitur Baru Backlog')
            ->set('prioritas', Feature::PRIORITY_TINGGI)
            ->call('saveFeature')
            ->assertSee('Fitur Baru Backlog');

        // Pindah fitur dari backlog ke Dikerjakan
        $component
            ->call('requestStatusChange', $high->id, Feature::STATUS_DIKERJAKAN);

        $high->refresh();
        $this->assertSame(Feature::STATUS_DIKERJAKAN, $high->status);
        $this->assertNotNull($high->tgl_mulai);
    }

    public function test_history_tab_displays_feature_logs_for_this_project_only(): void
    {
        $admin = User::create([
            'nama' => 'Admin Log',
            'email' => 'adminlog@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $projectA = Project::create(['nama' => 'Project A', 'status' => Project::STATUS_BERJALAN, 'pic_id' => $admin->id]);
        $projectB = Project::create(['nama' => 'Project B', 'status' => Project::STATUS_BERJALAN, 'pic_id' => $admin->id]);

        $featureA = Feature::create(['project_id' => $projectA->id, 'nama' => 'Fitur Alpha', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => 'sedang', 'urutan' => 1]);
        $featureB = Feature::create(['project_id' => $projectB->id, 'nama' => 'Fitur Beta', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => 'sedang', 'urutan' => 1]);

        FeatureLog::create([
            'feature_id' => $featureA->id,
            'status_lama' => Feature::STATUS_BELUM_MULAI,
            'status_baru' => Feature::STATUS_DIKERJAKAN,
            'user_id' => $admin->id,
            'created_at' => now(),
        ]);

        FeatureLog::create([
            'feature_id' => $featureB->id,
            'status_lama' => Feature::STATUS_BELUM_MULAI,
            'status_baru' => Feature::STATUS_SELESAI,
            'user_id' => $admin->id,
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ProjectShow::class, ['project' => $projectA])
            ->call('setViewMode', 'riwayat')
            ->assertSee('Fitur Alpha')
            ->assertSee('Belum Mulai')
            ->assertSee('Dikerjakan')
            ->assertSee('Admin Log')
            ->assertDontSee('Fitur Beta');
    }
}
