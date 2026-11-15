<?php

namespace App\Livewire\Account;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Pengaturan Akun')]
class AccountSettings extends Component
{
    use WithFileUploads;

    public $photo = null;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo', $this->photoRules(), $this->photoMessages());
    }

    public function updatePhoto(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $this->validate($this->photoRules(), $this->photoMessages());

        $oldPath = $user->avatar_path;
        $newPath = $this->photo->store("avatars/{$user->id}", 'local');

        $user->forceFill(['avatar_path' => $newPath])->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        $this->photo = null;
        $this->resetValidation('photo');

        session()->flash('success', 'Foto profil berhasil diperbarui.');
        $this->redirectRoute('account.settings');
    }

    public function removePhoto(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $oldPath = $user->avatar_path;
        if (! $oldPath) {
            return;
        }

        $user->forceFill(['avatar_path' => null])->save();
        Storage::disk('local')->delete($oldPath);

        $this->photo = null;
        $this->resetValidation('photo');

        session()->flash('success', 'Foto profil berhasil dihapus.');
        $this->redirectRoute('account.settings');
    }

    public function updatePassword(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        $user->forceFill(['password' => $validated['password']])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();

        session()->flash('password_success', 'Kata sandi berhasil diperbarui.');
    }

    protected function photoRules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function photoMessages(): array
    {
        return [
            'photo.required' => 'Pilih foto profil terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar yang valid.',
            'photo.mimes' => 'Format foto yang didukung: JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    public function render()
    {
        return view('livewire.account.account-settings', [
            'user' => auth()->user(),
        ]);
    }
}
