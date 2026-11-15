<div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Manajemen User</h1>
            <p class="text-secondary mb-0 small">Kelola akun pengguna, role, dan status aktif.</p>
        </div>
        <button type="button" wire:click="openCreate" class="btn btn-primary fw-semibold">
            + Tambah User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card-dasher p-3 mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    class="form-control"
                    placeholder="Cari nama atau email..."
                    aria-label="Cari user">
            </div>
            <div class="col-6 col-md-4">
                <select wire:model.live="filterRole" class="form-select" aria-label="Filter role">
                    <option value="">Semua Role</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select wire:model.live="filterStatus" class="form-select" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-dasher overflow-hidden">
        <div class="table-responsive-cards">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="ps-3 fw-semibold table-cell-primary">{{ $user->nama }}</td>
                            <td class="text-secondary text-break" data-label="Email">{{ $user->email }}</td>
                            <td data-label="Role">
                                @php
                                    $roleBadge = match ($user->role) {
                                        'admin' => 'bg-danger-subtle-ds',
                                        'pm' => 'bg-info-subtle-ds',
                                        default => 'bg-primary-subtle-ds',
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }}">{{ $user->roleLabel() }}</span>
                            </td>
                            <td data-label="Status">
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle-ds">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3 table-cell-actions" data-label="Aksi">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-1 table-action-group">
                                    <button type="button" wire:click="openEdit({{ $user->id }})" class="btn btn-sm btn-light border">
                                        Ubah
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button
                                            type="button"
                                            wire:click="toggleActive({{ $user->id }})"
                                            class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Tidak ada data user yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <form wire:submit="save">
                        <div class="modal-header border-bottom">
                            <h2 class="modal-title h6 fw-bold mb-0">
                                {{ $editingId ? 'Ubah User' : 'Tambah User' }}
                            </h2>
                            <button type="button" wire:click="closeModal" class="btn-close" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="user-nama" class="form-label small fw-semibold">Nama</label>
                                <input id="user-nama" type="text" wire:model="nama" class="form-control @error('nama') is-invalid @enderror">
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="user-email" class="form-label small fw-semibold">Email</label>
                                <input id="user-email" type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="user-password" class="form-label small fw-semibold">
                                    Kata Sandi
                                    @if($editingId)
                                        <span class="text-secondary fw-normal">(kosongkan jika tidak diubah)</span>
                                    @endif
                                </label>
                                <input id="user-password" type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="user-role" class="form-label small fw-semibold">Role</label>
                                <select id="user-role" wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                    @foreach($roles as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check">
                                <input id="user-active" type="checkbox" wire:model="is_active" class="form-check-input @error('is_active') is-invalid @enderror">
                                <label for="user-active" class="form-check-label small">Akun aktif</label>
                                @error('is_active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" wire:click="closeModal" class="btn btn-light border">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
