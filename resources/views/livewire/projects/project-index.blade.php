<div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Daftar Project</h1>
            <p class="text-secondary mb-0 small">Pantau status, tenggat waktu, anggota, dan progres setiap project.</p>
        </div>
        @can('create', \App\Models\Project::class)
            <button type="button" wire:click="openCreate" class="btn btn-primary fw-semibold">
                + Tambah Project
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card-dasher p-3 mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    class="form-control"
                    placeholder="Cari nama atau deskripsi project..."
                    aria-label="Cari project">
            </div>
            <div class="col-12 col-md-3">
                <select wire:model.live="filterStatus" class="form-select" aria-label="Filter status project">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card-dasher overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Project</th>
                        <th>Status</th>
                        <th>PIC</th>
                        <th>Deadline</th>
                        <th style="min-width: 150px;">Progress</th>
                        <th>Anggota</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php
                            $progress = $project->progressPercent();
                            $statusBadge = match ($project->status) {
                                'berjalan' => 'bg-info-subtle-ds',
                                'selesai' => 'bg-success-subtle-ds',
                                'ditunda' => 'bg-warning-subtle-ds',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                        @endphp
                        <tr wire:key="project-{{ $project->id }}">
                            <td class="ps-3">
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ $project->nama }}
                                </a>
                                @if($project->deskripsi)
                                    <div class="text-secondary small text-truncate" style="max-width: 280px;">
                                        {{ $project->deskripsi }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge }}">{{ $project->statusLabel() }}</span>
                            </td>
                            <td class="small">{{ $project->pic?->nama ?? '-' }}</td>
                            <td class="small">
                                @if($project->tgl_target)
                                    <span>{{ $project->tgl_target->format('d M Y') }}</span>
                                    @if($project->isOverdue())
                                        <span class="badge bg-danger-subtle-ds ms-1">Terlambat</span>
                                    @endif
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 7px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $progress }}%</span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.75rem;">
                                    {{ $project->selesai_features_count }}/{{ $project->features_count }} fitur
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $project->members_count }} orang</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light border">
                                        Buka
                                    </a>
                                    @can('manageMembers', $project)
                                        <button type="button" wire:click="openMembers({{ $project->id }})" class="btn btn-sm btn-light border">
                                            Anggota
                                        </button>
                                    @endcan
                                    @can('update', $project)
                                        <button type="button" wire:click="openEdit({{ $project->id }})" class="btn btn-sm btn-light border">
                                            Ubah
                                        </button>
                                    @endcan
                                    @can('delete', $project)
                                        <button
                                            type="button"
                                            wire:click="delete({{ $project->id }})"
                                            wire:confirm="Yakin hapus project '{{ $project->nama }}'? Semua fitur di dalamnya juga akan terhapus."
                                            class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">
                                Belum ada project yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="p-3 border-top">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah / Ubah Project --}}
    @if($showFormModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <form wire:submit="save">
                        <div class="modal-header border-bottom">
                            <h2 class="modal-title h6 fw-bold mb-0">
                                {{ $editingId ? 'Ubah Project' : 'Tambah Project' }}
                            </h2>
                            <button type="button" wire:click="closeFormModal" class="btn-close" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="project-nama" class="form-label small fw-semibold">Nama Project</label>
                                    <input id="project-nama" type="text" wire:model="nama" class="form-control @error('nama') is-invalid @enderror">
                                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label for="project-deskripsi" class="form-label small fw-semibold">Deskripsi</label>
                                    <textarea id="project-deskripsi" rows="3" wire:model="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"></textarea>
                                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="project-status" class="form-label small fw-semibold">Status</label>
                                    <select id="project-status" wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="project-pic" class="form-label small fw-semibold">PIC Project</label>
                                    <select id="project-pic" wire:model="pic_id" class="form-select @error('pic_id') is-invalid @enderror">
                                        <option value="">-- Pilih PIC --</option>
                                        @foreach($activeUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->roleLabel() }})</option>
                                        @endforeach
                                    </select>
                                    @error('pic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="project-tgl-mulai" class="form-label small fw-semibold">Tanggal Mulai</label>
                                    <input id="project-tgl-mulai" type="date" wire:model="tgl_mulai" class="form-control @error('tgl_mulai') is-invalid @enderror">
                                    @error('tgl_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="project-tgl-target" class="form-label small fw-semibold">Tanggal Target (Deadline)</label>
                                    <input id="project-tgl-target" type="date" wire:model="tgl_target" class="form-control @error('tgl_target') is-invalid @enderror">
                                    @error('tgl_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Anggota Tim yang Dapat Mengakses</label>
                                    <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                                        <div class="row g-2">
                                            @foreach($activeUsers as $u)
                                                <div class="col-sm-6">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            value="{{ $u->id }}"
                                                            wire:model="selectedMembers"
                                                            id="member-form-{{ $u->id }}">
                                                        <label class="form-check-label small" for="member-form-{{ $u->id }}">
                                                            {{ $u->nama }} <span class="text-secondary">({{ $u->roleLabel() }})</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-text">PIC otomatis dimasukkan sebagai anggota project.</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" wire:click="closeFormModal" class="btn btn-light border">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Kelola Anggota --}}
    @if($showMembersModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <form wire:submit="saveMembers">
                        <div class="modal-header border-bottom">
                            <h2 class="modal-title h6 fw-bold mb-0">Anggota: {{ $managingProjectName }}</h2>
                            <button type="button" wire:click="closeMembersModal" class="btn-close" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-secondary small mb-2">Pilih user yang memiliki akses ke project ini:</p>
                            <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                                @foreach($activeUsers as $u)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            value="{{ $u->id }}"
                                            wire:model="selectedMembers"
                                            id="member-manage-{{ $u->id }}">
                                        <label class="form-check-label small" for="member-manage-{{ $u->id }}">
                                            <span class="fw-semibold">{{ $u->nama }}</span>
                                            <span class="text-secondary">({{ $u->roleLabel() }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" wire:click="closeMembersModal" class="btn btn-light border">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Simpan Anggota</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
