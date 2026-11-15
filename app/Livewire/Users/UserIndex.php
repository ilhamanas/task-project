<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen User')]
class UserIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $filterRole = '';
    public string $filterStatus = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $nama = '';
    public string $email = '';
    public string $password = '';
    public string $role = User::ROLE_MEMBER;
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorizeAdmin();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorizeAdmin();
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->nama = $user->nama;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'password' => $this->editingId ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'is_active' => ['boolean'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.in' => 'Role tidak valid.',
        ]);

        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ];

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            if ($user->id === auth()->id() && ! $data['is_active']) {
                $this->addError('is_active', 'Anda tidak dapat menonaktifkan akun sendiri.');
                return;
            }
            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }
            $user->update($data);
            session()->flash('success', 'Data user berhasil diperbarui.');
        } else {
            $data['password'] = $validated['password'];
            User::create($data);
            session()->flash('success', 'User baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        session()->flash(
            'success',
            $user->is_active ? "User {$user->nama} diaktifkan." : "User {$user->nama} dinonaktifkan."
        );
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nama = '';
        $this->email = '';
        $this->password = '';
        $this->role = User::ROLE_MEMBER;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('nama', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->filterRole !== '', fn ($q) => $q->where('role', $this->filterRole))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('is_active', $this->filterStatus === 'aktif'))
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.users.user-index', [
            'users' => $users,
            'roles' => User::ROLES,
        ]);
    }
}
