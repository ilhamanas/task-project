<?php

namespace Tests\Feature;

use App\Livewire\Projects\ProjectShow;
use App\Models\Feature;
use App\Models\FeatureComment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_add_and_delete_own_comment_while_other_member_cannot_delete_it(): void
    {
        $admin = User::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $member1 = User::create([
            'nama' => 'Anggota Satu',
            'email' => 'm1@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $member2 = User::create([
            'nama' => 'Anggota Dua',
            'email' => 'm2@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Komentar',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $admin->id,
        ]);
        $project->members()->attach([$member1->id, $member2->id]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'nama' => 'Integrasi Pembayaran',
            'status' => Feature::STATUS_DIKERJAKAN,
            'prioritas' => Feature::PRIORITY_TINGGI,
            'urutan' => 1,
        ]);

        // Member 1 menambahkan komentar
        Livewire::actingAs($member1)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->set('commentText', '')
            ->call('addComment')
            ->assertHasErrors('commentText')
            ->set('commentText', 'Endpoint sandbox sudah siap diuji.')
            ->call('addComment')
            ->assertHasNoErrors()
            ->assertSee('Endpoint sandbox sudah siap diuji.')
            ->assertSee('1 komentar');

        $comment = FeatureComment::where('feature_id', $feature->id)->firstOrFail();

        // Member 2 tidak boleh menghapus komentar Member 1
        Livewire::actingAs($member2)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->call('deleteComment', $comment->id)
            ->assertForbidden();

        // Member 1 (pembuat) dapat menghapus komentarnya sendiri
        Livewire::actingAs($member1)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('feature_comments', ['id' => $comment->id]);
    }
}
