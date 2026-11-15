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

class FeatureCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_feature_starts_in_belum_mulai_and_status_transitions_follow_rules(): void
    {
        $member = User::create([
            'nama' => 'Rina',
            'email' => 'rina@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Aplikasi Absensi',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $member->id,
        ]);
        $project->members()->attach($member->id);

        // Tambah fitur baru -> otomatis Belum Mulai & tercatat di feature_logs
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openCreateFeature')
            ->set('nama', 'Login Face ID')
            ->set('prioritas', Feature::PRIORITY_TINGGI)
            ->set('pic_id', (string) $member->id)
            ->call('saveFeature')
            ->assertHasNoErrors();

        $feature = Feature::where('nama', 'Login Face ID')->firstOrFail();
        $this->assertSame(Feature::STATUS_BELUM_MULAI, $feature->status);
        $this->assertNull($feature->tgl_mulai);
        $this->assertDatabaseHas('feature_logs', [
            'feature_id' => $feature->id,
            'status_lama' => null,
            'status_baru' => Feature::STATUS_BELUM_MULAI,
            'user_id' => $member->id,
        ]);

        // Pindah ke Dikerjakan -> tgl_mulai terisi otomatis
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('requestStatusChange', $feature->id, Feature::STATUS_DIKERJAKAN);

        $feature->refresh();
        $this->assertSame(Feature::STATUS_DIKERJAKAN, $feature->status);
        $this->assertNotNull($feature->tgl_mulai);

        // Pindah ke Selesai -> tgl_selesai terisi otomatis, progress 100%
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('requestStatusChange', $feature->id, Feature::STATUS_SELESAI)
            ->assertViewHas('progress', 100);

        $feature->refresh();
        $this->assertSame(Feature::STATUS_SELESAI, $feature->status);
        $this->assertNotNull($feature->tgl_selesai);

        // Kembalikan Selesai ke Dikerjakan -> tgl_selesai dikosongkan
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('requestStatusChange', $feature->id, Feature::STATUS_DIKERJAKAN)
            ->assertViewHas('progress', 0);

        $feature->refresh();
        $this->assertNull($feature->tgl_selesai);

        // Pindah ke Gagal -> wajib alasan gagal
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('requestStatusChange', $feature->id, Feature::STATUS_GAGAL)
            ->assertSet('showFailModal', true)
            ->set('catatan_gagal', '')
            ->call('confirmFailStatus')
            ->assertHasErrors('catatan_gagal')
            ->set('catatan_gagal', 'API kamera belum tersedia')
            ->call('confirmFailStatus')
            ->assertHasNoErrors();

        $feature->refresh();
        $this->assertSame(Feature::STATUS_GAGAL, $feature->status);
        $this->assertSame('API kamera belum tersedia', $feature->catatan_gagal);

        // Reopen Gagal -> Dikerjakan, catatan_gagal tetap tersimpan sebagai riwayat
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('requestStatusChange', $feature->id, Feature::STATUS_DIKERJAKAN);

        $feature->refresh();
        $this->assertSame(Feature::STATUS_DIKERJAKAN, $feature->status);
        $this->assertSame('API kamera belum tersedia', $feature->catatan_gagal);
        $this->assertSame(6, FeatureLog::where('feature_id', $feature->id)->count());
    }

    public function test_progress_counts_failed_features_in_total(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Hitung Progress',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
        ]);

        Feature::create(['project_id' => $project->id, 'nama' => 'F1', 'status' => Feature::STATUS_SELESAI, 'prioritas' => 'sedang', 'urutan' => 1]);
        Feature::create(['project_id' => $project->id, 'nama' => 'F2', 'status' => Feature::STATUS_GAGAL, 'prioritas' => 'sedang', 'catatan_gagal' => 'Batal', 'urutan' => 2]);

        // 1 selesai dari total 2 fitur (gagal tetap dihitung di total) = 50%
        Livewire::actingAs($admin)
            ->test(ProjectShow::class, ['project' => $project])
            ->assertViewHas('totalFeatures', 2)
            ->assertViewHas('progress', 50);
    }

    public function test_kanban_drag_move_and_manual_ordering_update_database_and_view_mode(): void
    {
        $member = User::create([
            'nama' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Kanban',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $member->id,
        ]);
        $project->members()->attach($member->id);

        $f1 = Feature::create(['project_id' => $project->id, 'nama' => 'Fitur Satu', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => 'tinggi', 'urutan' => 1]);
        $f2 = Feature::create(['project_id' => $project->id, 'nama' => 'Fitur Dua', 'status' => Feature::STATUS_BELUM_MULAI, 'prioritas' => 'sedang', 'urutan' => 2]);

        // Switch mode kanban / tabel + reorder dalam satu kolom + pindah antar kolom
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->assertSee('Belum Mulai')
            ->assertSee('Dikerjakan')
            ->assertSee('Selesai')
            ->assertSee('Gagal')
            ->call('moveFeature', $f2->id, Feature::STATUS_BELUM_MULAI, [$f2->id, $f1->id])
            ->call('moveFeature', $f1->id, Feature::STATUS_DIKERJAKAN, [$f1->id])
            ->call('setViewMode', 'tabel')
            ->assertSet('viewMode', 'tabel');

        $this->assertSame(1, $f2->fresh()->urutan);
        $this->assertSame(Feature::STATUS_DIKERJAKAN, $f1->fresh()->status);
        $this->assertNotNull($f1->fresh()->tgl_mulai);
    }
}
