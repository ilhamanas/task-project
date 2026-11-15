<?php

namespace Tests\Feature;

use App\Livewire\Projects\ProjectShow;
use App\Models\Feature;
use App\Models\FeatureAttachment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_upload_download_and_delete_attachment_while_non_member_is_denied(): void
    {
        Storage::fake('local');

        $member = User::create([
            'nama' => 'Anggota Upload',
            'email' => 'uploader@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $otherMember = User::create([
            'nama' => 'Anggota Lain',
            'email' => 'other@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $outsider = User::create([
            'nama' => 'Bukan Anggota',
            'email' => 'outsider@example.com',
            'password' => 'password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $project = Project::create([
            'nama' => 'Project Lampiran',
            'status' => Project::STATUS_BERJALAN,
            'pic_id' => $member->id,
        ]);
        $project->members()->attach([$member->id, $otherMember->id]);

        $feature = Feature::create([
            'project_id' => $project->id,
            'nama' => 'Mockup Halaman Utama',
            'status' => Feature::STATUS_DIKERJAKAN,
            'prioritas' => Feature::PRIORITY_TINGGI,
            'urutan' => 1,
        ]);

        // Format berbahaya ditolak validasi
        $badFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->set('attachmentFile', $badFile)
            ->call('uploadAttachment')
            ->assertHasErrors('attachmentFile');

        // Upload file PDF valid
        $pdf = UploadedFile::fake()->create('spesifikasi.pdf', 200, 'application/pdf');
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->set('attachmentFile', $pdf)
            ->call('uploadAttachment')
            ->assertHasNoErrors()
            ->assertSee('spesifikasi.pdf');

        $attachment = FeatureAttachment::where('feature_id', $feature->id)->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);

        // Anggota project bisa mengunduh; bukan anggota mendapat 403
        $this->actingAs($member)->get(route('attachments.download', $attachment))->assertOk();
        $this->actingAs($outsider)->get(route('attachments.download', $attachment))->assertForbidden();

        // Anggota lain yang bukan pengunggah/PM/Admin tidak bisa hapus
        Livewire::actingAs($otherMember)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->call('deleteAttachment', $attachment->id)
            ->assertForbidden();

        // Pengunggah bisa hapus lampiran beserta file fisiknya
        Livewire::actingAs($member)
            ->test(ProjectShow::class, ['project' => $project])
            ->call('openDetailFeature', $feature->id)
            ->call('deleteAttachment', $attachment->id);

        $this->assertDatabaseMissing('feature_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($attachment->path);
    }
}
