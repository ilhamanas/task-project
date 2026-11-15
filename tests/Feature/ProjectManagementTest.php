<?php

namespace Tests\Feature;

use App\Livewire\Projects\ProjectIndex;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'nama' => ucfirst($role) . ' User',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_non_member_is_denied_access_to_project_url_and_filtered_from_list(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@example.com');
        $memberAllowed = $this->createUser(User::ROLE_MEMBER, 'member1@example.com');
        $memberDenied = $this->createUser(User::ROLE_MEMBER, 'member2@example.com');

        $projectA = Project::create([
            'nama' => 'Project Rahasia A',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
        ]);
        $projectA->members()->attach($memberAllowed->id);

        $projectB = Project::create([
            'nama' => 'Project Lain B',
            'status' => Project::STATUS_PLANNING,
            'pic_id' => $admin->id,
        ]);

        // Admin bisa lihat semua dan akses semua URL
        $this->actingAs($admin)->get('/projects')
            ->assertOk()
            ->assertSee('Project Rahasia A')
            ->assertSee('Project Lain B');
        $this->actingAs($admin)->get('/projects/' . $projectB->id)->assertOk();

        // Member hanya melihat project tempat ia menjadi anggota
        $this->actingAs($memberAllowed)->get('/projects')
            ->assertOk()
            ->assertSee('Project Rahasia A')
            ->assertDontSee('Project Lain B');

        $this->actingAs($memberAllowed)->get('/projects/' . $projectA->id)->assertOk();
        $this->actingAs($memberAllowed)->get('/projects/' . $projectB->id)->assertForbidden();
        $this->actingAs($memberDenied)->get('/projects/' . $projectA->id)->assertForbidden();
    }

    public function test_admin_and_pm_can_crud_project_and_assign_members_while_member_cannot(): void
    {
        $pm = $this->createUser(User::ROLE_PM, 'pm@example.com');
        $member = $this->createUser(User::ROLE_MEMBER, 'member@example.com');

        // Member biasa tidak boleh membuat project
        Livewire::actingAs($member)
            ->test(ProjectIndex::class)
            ->call('openCreate')
            ->assertForbidden();

        // PM dapat membuat project dan menugaskan anggota
        Livewire::actingAs($pm)
            ->test(ProjectIndex::class)
            ->call('openCreate')
            ->set('nama', 'Aplikasi Inventory')
            ->set('deskripsi', 'Sistem stok gudang')
            ->set('status', Project::STATUS_BERJALAN)
            ->set('pic_id', (string) $pm->id)
            ->set('tgl_mulai', '2026-11-01')
            ->set('tgl_target', '2026-11-30')
            ->set('selectedMembers', [$member->id])
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::where('nama', 'Aplikasi Inventory')->first();
        $this->assertNotNull($project);
        $this->assertTrue($project->members()->where('users.id', $pm->id)->exists());
        $this->assertTrue($project->members()->where('users.id', $member->id)->exists());

        // Member tidak boleh hapus project
        Livewire::actingAs($member)
            ->test(ProjectIndex::class)
            ->call('delete', $project->id)
            ->assertForbidden();

        // PM dapat menghapus project miliknya
        Livewire::actingAs($pm)
            ->test(ProjectIndex::class)
            ->call('delete', $project->id);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
