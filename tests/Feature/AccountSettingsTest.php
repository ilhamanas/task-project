<?php

namespace Tests\Feature;

use App\Livewire\Account\AccountSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_account_settings_or_avatar(): void
    {
        $user = User::create([
            'nama' => 'Pengguna Test',
            'email' => 'pengguna@example.com',
            'password' => 'password123',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $this->get('/account')->assertRedirect('/login');
        $this->get(route('users.avatar', $user))->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_account_setting_link_in_header_and_can_open_page(): void
    {
        $user = User::create([
            'nama' => 'Ilham Annas',
            'email' => 'ilham@example.com',
            'password' => 'password123',
            'role' => User::ROLE_PM,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Pengaturan Akun')
            ->assertSee(route('account.settings'), false);

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('Pengaturan Akun')
            ->assertSee('Foto Profil')
            ->assertSee('Ubah Kata Sandi');
    }

    public function test_user_can_upload_replace_and_remove_profile_photo(): void
    {
        Storage::fake('local');

        $user = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        // Belum punya foto -> route avatar mengembalikan 404
        $this->actingAs($user)->get(route('users.avatar', $user))->assertNotFound();

        // Format non-gambar ditolak
        $badFile = UploadedFile::fake()->create('script.php', 10, 'application/x-php');
        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('photo', $badFile)
            ->call('updatePhoto')
            ->assertHasErrors('photo');

        // Upload foto pertama
        $firstPhoto = UploadedFile::fake()->image('avatar-1.png', 200, 200);
        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('photo', $firstPhoto)
            ->call('updatePhoto')
            ->assertHasNoErrors()
            ->assertRedirect(route('account.settings'));

        $firstPath = $user->fresh()->avatar_path;
        $this->assertNotNull($firstPath);
        Storage::disk('local')->assertExists($firstPath);

        $this->actingAs($user->fresh())
            ->get(route('users.avatar', $user))
            ->assertOk();

        // Topbar menampilkan URL avatar baru
        $this->actingAs($user->fresh())
            ->get('/account')
            ->assertOk()
            ->assertSee($user->fresh()->avatarUrl(), false)
            ->assertSee('Hapus Foto');

        // Upload foto pengganti -> foto lama ikut terhapus
        $secondPhoto = UploadedFile::fake()->image('avatar-2.jpg', 240, 240);
        Livewire::actingAs($user->fresh())
            ->test(AccountSettings::class)
            ->set('photo', $secondPhoto)
            ->call('updatePhoto')
            ->assertHasNoErrors()
            ->assertRedirect(route('account.settings'));

        $secondPath = $user->fresh()->avatar_path;
        $this->assertNotNull($secondPath);
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($secondPath);

        // Hapus foto profil
        Livewire::actingAs($user->fresh())
            ->test(AccountSettings::class)
            ->call('removePhoto')
            ->assertRedirect(route('account.settings'));

        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertMissing($secondPath);
        $this->actingAs($user->fresh())->get(route('users.avatar', $user))->assertNotFound();
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $user = User::create([
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        // Kata sandi saat ini salah -> ditolak
        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('current_password', 'salah-banget')
            ->set('password', 'barulebihaman')
            ->set('password_confirmation', 'barulebihaman')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));

        // Konfirmasi tidak cocok & sama dengan kata sandi lama -> ditolak
        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('current_password', 'rahasia123')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'beda-konfirmasi')
            ->call('updatePassword')
            ->assertHasErrors('password');

        // Berhasil ubah kata sandi
        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('current_password', 'rahasia123')
            ->set('password', 'sandibaru456')
            ->set('password_confirmation', 'sandibaru456')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('current_password', '')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '')
            ->assertSee('Kata sandi berhasil diperbarui.');

        $this->assertTrue(Hash::check('sandibaru456', $user->fresh()->password));
        $this->assertFalse(Hash::check('rahasia123', $user->fresh()->password));
    }
}
