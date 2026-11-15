<div>
    {{-- Header Detail Project --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <a href="{{ route('projects.index') }}" class="text-decoration-none text-secondary">Daftar Project</a>
                / {{ $project->nama }}
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold mb-0">{{ $project->nama }}</h1>
                @php
                    $projectBadge = match ($project->status) {
                        'berjalan' => 'bg-info-subtle-ds',
                        'selesai' => 'bg-success-subtle-ds',
                        'ditunda' => 'bg-warning-subtle-ds',
                        default => 'bg-secondary-subtle text-secondary',
                    };
                @endphp
                <span class="badge {{ $projectBadge }}">{{ $project->statusLabel() }}</span>
                @if($project->isOverdue())
                    <span class="badge bg-danger-subtle-ds">Terlambat</span>
                @endif
            </div>
            @if($project->deskripsi)
                <p class="text-secondary small mb-1">{{ $project->deskripsi }}</p>
            @endif
            <div class="text-secondary small d-flex flex-wrap gap-3">
                <span>PIC: <strong class="text-dark">{{ $project->pic?->nama ?? '-' }}</strong></span>
                @if($project->tgl_mulai)
                    <span>Mulai: <strong class="text-dark">{{ $project->tgl_mulai->format('d M Y') }}</strong></span>
                @endif
                @if($project->tgl_target)
                    <span>Target: <strong class="text-dark">{{ $project->tgl_target->format('d M Y') }}</strong></span>
                @endif
                <span>Anggota: <strong class="text-dark">{{ $members->count() }} orang</strong></span>
            </div>
        </div>
        <button type="button" wire:click="openCreateFeature" class="btn btn-primary fw-semibold">
            {{ $viewMode === 'backlog' ? '+ Tambah ke Backlog' : '+ Tambah Fitur' }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- 4 Kartu Angka & Progress Bar --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-warning p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Belum Mulai</div>
                <div class="fs-2 fw-bold">{{ $statusCounts['belum_mulai'] }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-info p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Dikerjakan</div>
                <div class="fs-2 fw-bold">{{ $statusCounts['dikerjakan'] }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-success p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Selesai</div>
                <div class="fs-2 fw-bold">{{ $statusCounts['selesai'] }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-danger p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Gagal</div>
                <div class="fs-2 fw-bold">{{ $statusCounts['gagal'] }}</div>
            </div>
        </div>
    </div>

    <div class="card-dasher p-3 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2">
            <span class="small fw-semibold">Progress Project</span>
            <span class="small fw-bold text-primary-ds">
                {{ $progress }}% ({{ $statusCounts['selesai'] }} dari {{ $totalFeatures }} fitur selesai)
            </span>
        </div>
        <div class="progress" style="height: 10px;">
            <div
                class="progress-bar"
                role="progressbar"
                style="width: {{ $progress }}%;"
                aria-valuenow="{{ $progress }}"
                aria-valuemin="0"
                aria-valuemax="100"></div>
        </div>
    </div>

    {{-- Toolbar Switch Tampilan (Kanban / Tabel / Backlog / Riwayat) & Pencarian --}}
    <div class="card-dasher p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="ds-filter-pills" role="group" aria-label="Mode tampilan project">
                <button
                    type="button"
                    wire:click="setViewMode('kanban')"
                    class="btn btn-sm {{ $viewMode === 'kanban' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                    Papan Kanban
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('tabel')"
                    class="btn btn-sm {{ $viewMode === 'tabel' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                    Daftar Tabel
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('backlog')"
                    class="btn btn-sm {{ $viewMode === 'backlog' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                    Backlog ({{ $statusCounts['belum_mulai'] }})
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('riwayat')"
                    class="btn btn-sm {{ $viewMode === 'riwayat' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                    Riwayat
                </button>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 toolbar-filters">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="searchFeature"
                    class="form-control form-control-sm toolbar-search"
                    placeholder="Cari nama fitur..."
                    aria-label="Cari fitur">

                @if($viewMode === 'tabel')
                    <select wire:model.live="filterStatus" class="form-select form-select-sm toolbar-select" aria-label="Filter status fitur">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif($viewMode === 'backlog')
                    <select wire:model.live="filterPriority" class="form-select form-select-sm toolbar-select" aria-label="Filter prioritas backlog">
                        <option value="">Semua Prioritas</option>
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
    </div>

    {{-- Tampilan Kanban 4 Kolom --}}
    @if($viewMode === 'kanban')
        <p class="small text-secondary d-lg-none mb-2">Geser ke samping untuk melihat kolom status lainnya.</p>
        <div class="kanban-board" id="kanbanBoard">
            @foreach($statuses as $statusKey => $statusLabel)
                @php
                    $items = $kanbanColumns->get($statusKey, collect());
                    $dotClass = match ($statusKey) {
                        'dikerjakan' => 'bg-info',
                        'selesai' => 'bg-success',
                        'gagal' => 'bg-danger',
                        default => 'bg-warning',
                    };
                @endphp
                <section class="kanban-column" wire:key="kanban-col-{{ $statusKey }}" aria-label="Kolom {{ $statusLabel }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block {{ $dotClass }}" style="width: 9px; height: 9px;"></span>
                            <h2 class="h6 fw-bold mb-0">{{ $statusLabel }}</h2>
                        </div>
                        <span class="badge bg-white text-dark border rounded-pill">{{ $items->count() }}</span>
                    </div>

                    <div
                        class="kanban-list"
                        data-status="{{ $statusKey }}"
                        x-data
                        x-init="window.initTaskKanbanColumn && window.initTaskKanbanColumn($el, $wire)">
                        @foreach($items as $feature)
                            @php
                                $priorityBadge = match ($feature->prioritas) {
                                    'tinggi' => 'bg-danger-subtle-ds',
                                    'sedang' => 'bg-warning-subtle-ds',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp
                            <article
                                class="kanban-card"
                                data-feature-id="{{ $feature->id }}"
                                wire:key="kanban-card-{{ $feature->id }}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge {{ $priorityBadge }}">{{ $feature->priorityLabel() }}</span>
                                    <div class="dropdown">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border-0 py-0 px-2 text-secondary"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            aria-label="Opsi fitur {{ $feature->nama }}">
                                            &hellip;
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                                            <li>
                                                <button type="button" class="dropdown-item" wire:click="openDetailFeature({{ $feature->id }})">
                                                    Detail &amp; Komentar
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" wire:click="openEditFeature({{ $feature->id }})">
                                                    Ubah Fitur
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li class="dropdown-header py-1">Pindah Status</li>
                                            @foreach($statuses as $targetKey => $targetLabel)
                                                @if($targetKey !== $feature->status)
                                                    <li>
                                                        <button
                                                            type="button"
                                                            class="dropdown-item"
                                                            wire:click="requestStatusChange({{ $feature->id }}, '{{ $targetKey }}')">
                                                            Ke {{ $targetLabel }}
                                                        </button>
                                                    </li>
                                                @endif
                                            @endforeach
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dropdown-item text-danger"
                                                    wire:click="deleteFeature({{ $feature->id }})"
                                                    wire:confirm="Yakin hapus fitur '{{ $feature->nama }}'?">
                                                    Hapus Fitur
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <h3 class="h6 fw-semibold mb-1" style="font-size: 0.9375rem;">
                                    <button
                                        type="button"
                                        wire:click="openDetailFeature({{ $feature->id }})"
                                        class="btn btn-link p-0 text-start text-dark fw-semibold text-decoration-none">
                                        {{ $feature->nama }}
                                    </button>
                                </h3>

                                @if($feature->deskripsi)
                                    <p class="text-secondary small mb-2">{{ $feature->deskripsi }}</p>
                                @endif

                                @if($feature->catatan_gagal)
                                    <div class="small text-danger bg-danger-subtle-ds rounded p-2 mb-2">
                                        <strong>Catatan gagal:</strong> {{ $feature->catatan_gagal }}
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top small text-secondary" style="border-top-style: dashed !important;">
                                    <span>{{ $feature->pic?->nama ?? 'Belum ada PIC' }}</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="openDetailFeature({{ $feature->id }})"
                                            class="btn btn-link btn-sm p-0 text-secondary text-decoration-none"
                                            title="Buka detail, komentar, dan lampiran">
                                            {{ $feature->comments_count }} komentar
                                            @if($feature->attachments_count > 0)
                                                &middot; {{ $feature->attachments_count }} file
                                            @endif
                                        </button>
                                        @if($feature->tgl_target)
                                            <span>&middot; {{ $feature->tgl_target->format('d M') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        @if($items->isEmpty())
                            <div class="text-center text-secondary small py-4 border border-dashed rounded-3 bg-white bg-opacity-50 kanban-empty">
                                Tarik kartu ke sini
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @elseif($viewMode === 'tabel')
        {{-- Tampilan Tabel Daftar Fitur --}}
        <div class="card-dasher overflow-hidden">
            <div class="table-responsive-cards">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Fitur</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>PIC</th>
                            <th>Target</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($features as $feature)
                            @php
                                $priorityBadge = match ($feature->prioritas) {
                                    'tinggi' => 'bg-danger-subtle-ds',
                                    'sedang' => 'bg-warning-subtle-ds',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp
                            <tr wire:key="feature-row-{{ $feature->id }}">
                                <td class="ps-3 table-cell-primary">
                                    <div class="fw-semibold">{{ $feature->nama }}</div>
                                    @if($feature->deskripsi)
                                        <div class="text-secondary small">{{ $feature->deskripsi }}</div>
                                    @endif
                                    @if($feature->catatan_gagal)
                                        <div class="small text-danger mt-1">
                                            Catatan gagal: {{ $feature->catatan_gagal }}
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Prioritas">
                                    <span class="badge {{ $priorityBadge }}">{{ $feature->priorityLabel() }}</span>
                                </td>
                                <td class="feature-status-cell" data-label="Status">
                                    <select
                                        class="form-select form-select-sm"
                                        aria-label="Ubah status fitur {{ $feature->nama }}"
                                        wire:change="requestStatusChange({{ $feature->id }}, $event.target.value)">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" @selected($feature->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="small" data-label="PIC">{{ $feature->pic?->nama ?? '-' }}</td>
                                <td class="small" data-label="Target">
                                    {{ $feature->tgl_target?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="text-end pe-3 table-cell-actions" data-label="Aksi">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-1 table-action-group">
                                        <button type="button" wire:click="openDetailFeature({{ $feature->id }})" class="btn btn-sm btn-light border">
                                            Detail ({{ $feature->comments_count }})
                                        </button>
                                        <button type="button" wire:click="openEditFeature({{ $feature->id }})" class="btn btn-sm btn-light border">
                                            Ubah
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteFeature({{ $feature->id }})"
                                            wire:confirm="Yakin hapus fitur '{{ $feature->nama }}'?"
                                            class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    Belum ada fitur pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($viewMode === 'backlog')
        {{-- Tampilan Backlog (Fitur Belum Mulai, Urut Prioritas) --}}
        <div class="card-dasher overflow-hidden">
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Backlog Fitur</h2>
                    <p class="text-secondary small mb-0">Fitur berstatus Belum Mulai, diurutkan dari prioritas Tinggi ke Rendah.</p>
                </div>
                <button type="button" wire:click="openCreateFeature" class="btn btn-sm btn-primary fw-semibold">
                    + Tambah ke Backlog
                </button>
            </div>
            <div class="table-responsive-cards">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Fitur</th>
                            <th>Prioritas</th>
                            <th>PIC</th>
                            <th>Target</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backlogFeatures as $feature)
                            @php
                                $priorityBadge = match ($feature->prioritas) {
                                    'tinggi' => 'bg-danger-subtle-ds',
                                    'sedang' => 'bg-warning-subtle-ds',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp
                            <tr wire:key="backlog-row-{{ $feature->id }}">
                                <td class="ps-3 table-cell-primary">
                                    <div class="fw-semibold">{{ $feature->nama }}</div>
                                    @if($feature->deskripsi)
                                        <div class="text-secondary small">{{ $feature->deskripsi }}</div>
                                    @endif
                                </td>
                                <td data-label="Prioritas">
                                    <span class="badge {{ $priorityBadge }}">{{ $feature->priorityLabel() }}</span>
                                </td>
                                <td class="small" data-label="PIC">{{ $feature->pic?->nama ?? '-' }}</td>
                                <td class="small" data-label="Target">{{ $feature->tgl_target?->format('d M Y') ?? '-' }}</td>
                                <td class="text-end pe-3 table-cell-actions" data-label="Aksi">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-1 table-action-group">
                                        <button
                                            type="button"
                                            wire:click="requestStatusChange({{ $feature->id }}, 'dikerjakan')"
                                            class="btn btn-sm btn-primary fw-semibold">
                                            Mulai Kerjakan
                                        </button>
                                        <button type="button" wire:click="openDetailFeature({{ $feature->id }})" class="btn btn-sm btn-light border">
                                            Detail ({{ $feature->comments_count }})
                                        </button>
                                        <button type="button" wire:click="openEditFeature({{ $feature->id }})" class="btn btn-sm btn-light border">
                                            Ubah
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteFeature({{ $feature->id }})"
                                            wire:confirm="Yakin hapus fitur '{{ $feature->nama }}'?"
                                            class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Backlog kosong. Tambahkan fitur baru atau periksa filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Tampilan Riwayat Perubahan Status --}}
        <div class="card-dasher overflow-hidden">
            <div class="p-3 border-bottom">
                <h2 class="h6 fw-bold mb-0">Riwayat Perubahan Status</h2>
                <p class="text-secondary small mb-0">Catatan setiap perubahan status fitur pada project ini.</p>
            </div>
            <div class="table-responsive-cards">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Fitur</th>
                            <th>Perubahan Status</th>
                            <th class="pe-3">Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($featureLogs as $log)
                            @php
                                $oldLabel = $log->status_lama ? ($statuses[$log->status_lama] ?? $log->status_lama) : 'Baru Dibuat';
                                $newLabel = $statuses[$log->status_baru] ?? $log->status_baru;
                            @endphp
                            <tr wire:key="log-row-{{ $log->id }}">
                                <td class="ps-3 small text-secondary text-nowrap table-cell-primary">
                                    {{ $log->created_at?->format('d M Y, H:i') }}
                                </td>
                                <td class="fw-semibold" data-label="Fitur">{{ $log->feature?->nama ?? '-' }}</td>
                                <td data-label="Perubahan">
                                    <div class="d-inline-flex flex-wrap align-items-center gap-1">
                                        <span class="badge bg-light text-dark border">{{ $oldLabel }}</span>
                                        <span class="text-secondary">&rarr;</span>
                                        <span class="badge bg-primary-subtle-ds">{{ $newLabel }}</span>
                                    </div>
                                </td>
                                <td class="pe-3 small" data-label="Oleh">{{ $log->user?->nama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    Belum ada riwayat perubahan status.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($featureLogs && $featureLogs->hasPages())
                <div class="p-3 border-top">
                    {{ $featureLogs->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Tambah / Ubah Fitur --}}
    @if($showFeatureModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <form wire:submit="saveFeature">
                        <div class="modal-header border-bottom">
                            <h2 class="modal-title h6 fw-bold mb-0">
                                {{ $editingFeatureId ? 'Ubah Fitur' : 'Tambah Fitur' }}
                            </h2>
                            <button type="button" wire:click="closeFeatureModal" class="btn-close" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            @if(! $editingFeatureId)
                                <div class="alert alert-light border small py-2 mb-3">
                                    Fitur baru otomatis masuk ke status <strong>Belum Mulai</strong>.
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="feature-nama" class="form-label small fw-semibold">Nama Fitur</label>
                                <input id="feature-nama" type="text" wire:model="nama" class="form-control @error('nama') is-invalid @enderror">
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="feature-deskripsi" class="form-label small fw-semibold">Deskripsi</label>
                                <textarea id="feature-deskripsi" rows="3" wire:model="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"></textarea>
                                @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="feature-prioritas" class="form-label small fw-semibold">Prioritas</label>
                                    <select id="feature-prioritas" wire:model="prioritas" class="form-select @error('prioritas') is-invalid @enderror">
                                        @foreach($priorities as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('prioritas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="feature-target" class="form-label small fw-semibold">Tanggal Target</label>
                                    <input id="feature-target" type="date" wire:model="tgl_target" class="form-control @error('tgl_target') is-invalid @enderror">
                                    @error('tgl_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="feature-pic" class="form-label small fw-semibold">PIC Fitur</label>
                                <select id="feature-pic" wire:model="pic_id" class="form-select @error('pic_id') is-invalid @enderror">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->roleLabel() }})</option>
                                    @endforeach
                                </select>
                                @error('pic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" wire:click="closeFeatureModal" class="btn btn-light border">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Alasan Gagal --}}
    @if($showFailModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <form wire:submit="confirmFailStatus">
                        <div class="modal-header border-bottom">
                            <h2 class="modal-title h6 fw-bold mb-0">Catatan Fitur Gagal</h2>
                            <button type="button" wire:click="closeFailModal" class="btn-close" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-secondary mb-2">
                                Fitur: <strong class="text-dark">{{ $failingFeatureName }}</strong>
                            </p>
                            <div class="mb-2">
                                <label for="catatan-gagal" class="form-label small fw-semibold">Alasan Gagal <span class="text-danger">*</span></label>
                                <textarea
                                    id="catatan-gagal"
                                    rows="3"
                                    wire:model="catatan_gagal"
                                    class="form-control @error('catatan_gagal') is-invalid @enderror"
                                    placeholder="Jelaskan alasan fitur tidak berhasil atau dibatalkan..."></textarea>
                                @error('catatan_gagal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" wire:click="closeFailModal" class="btn btn-light border">Batal</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Simpan & Pindahkan ke Gagal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Detail & Komentar Fitur --}}
    @if($showDetailModal && $detailFeature)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" style="background-color: rgba(20, 26, 33, 0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                    <div class="modal-header border-bottom">
                        <div>
                            <h2 class="modal-title h6 fw-bold mb-1">{{ $detailFeature->nama }}</h2>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span class="badge bg-primary-subtle-ds">{{ $detailFeature->statusLabel() }}</span>
                                <span class="badge bg-light text-dark border">Prioritas: {{ $detailFeature->priorityLabel() }}</span>
                                <span class="text-secondary">PIC: {{ $detailFeature->pic?->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="btn-close" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @if($detailFeature->deskripsi)
                            <div class="mb-3">
                                <div class="small fw-semibold text-secondary mb-1">Deskripsi</div>
                                <p class="small mb-0">{{ $detailFeature->deskripsi }}</p>
                            </div>
                        @endif

                        @if($detailFeature->catatan_gagal)
                            <div class="alert alert-danger small py-2 mb-3">
                                <strong>Catatan gagal:</strong> {{ $detailFeature->catatan_gagal }}
                            </div>
                        @endif

                        <div class="row g-2 mb-4 small text-secondary">
                            <div class="col-12 col-sm-4">
                                <div class="border rounded p-2">
                                    <div>Target</div>
                                    <strong class="text-dark">{{ $detailFeature->tgl_target?->format('d M Y') ?? '-' }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="border rounded p-2">
                                    <div>Mulai</div>
                                    <strong class="text-dark">{{ $detailFeature->tgl_mulai?->format('d M Y') ?? '-' }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="border rounded p-2">
                                    <div>Selesai</div>
                                    <strong class="text-dark">{{ $detailFeature->tgl_selesai?->format('d M Y') ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h3 class="h6 fw-bold mb-3">Lampiran ({{ $detailFeature->attachments->count() }})</h3>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @forelse($detailFeature->attachments as $attachment)
                                <div class="border rounded-3 px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2" wire:key="attachment-{{ $attachment->id }}">
                                    <div class="small">
                                        <a href="{{ route('attachments.download', $attachment) }}" class="fw-semibold text-decoration-none text-dark">
                                            {{ $attachment->nama_file }}
                                        </a>
                                        <div class="text-secondary" style="font-size: 0.75rem;">
                                            {{ $attachment->formattedSize() }} &middot; Diunggah oleh {{ $attachment->user?->nama ?? 'User' }}
                                        </div>
                                    </div>
                                    <div class="d-inline-flex gap-2 align-items-center">
                                        <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-light border">
                                            Unduh
                                        </a>
                                        @if($attachment->user_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isPm())
                                            <button
                                                type="button"
                                                wire:click="deleteAttachment({{ $attachment->id }})"
                                                wire:confirm="Hapus lampiran '{{ $attachment->nama_file }}'?"
                                                class="btn btn-sm btn-outline-danger">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="small text-secondary mb-0">Belum ada lampiran pada fitur ini.</p>
                            @endforelse
                        </div>

                        <form wire:submit="uploadAttachment" class="mb-4">
                            <div class="d-flex flex-wrap gap-2 align-items-start">
                                <div class="flex-grow-1 min-w-0">
                                    <input
                                        type="file"
                                        wire:model="attachmentFile"
                                        class="form-control form-control-sm @error('attachmentFile') is-invalid @enderror"
                                        aria-label="Pilih file lampiran">
                                    @error('attachmentFile')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Maks. 5 MB (pdf, doc/x, xls/x, gambar, txt, zip).</div>
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-sm btn-primary fw-semibold"
                                    wire:loading.attr="disabled"
                                    wire:target="attachmentFile,uploadAttachment">
                                    <span wire:loading.remove wire:target="attachmentFile,uploadAttachment">Unggah Lampiran</span>
                                    <span wire:loading wire:target="attachmentFile,uploadAttachment">Mengunggah...</span>
                                </button>
                            </div>
                        </form>

                        <hr>

                        <h3 class="h6 fw-bold mb-3">Komentar ({{ $detailFeature->comments->count() }})</h3>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @forelse($detailFeature->comments as $comment)
                                <div class="border rounded-3 p-3 bg-light" wire:key="comment-{{ $comment->id }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="small">
                                            <strong class="text-dark">{{ $comment->user?->nama ?? 'User' }}</strong>
                                            <span class="text-secondary ms-1">&middot; {{ $comment->created_at?->format('d M Y, H:i') }}</span>
                                        </div>
                                        @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                                            <button
                                                type="button"
                                                wire:click="deleteComment({{ $comment->id }})"
                                                wire:confirm="Hapus komentar ini?"
                                                class="btn btn-link btn-sm text-danger p-0 text-decoration-none">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                    <div class="small text-dark" style="white-space: pre-line;">{{ $comment->isi }}</div>
                                </div>
                            @empty
                                <p class="small text-secondary mb-0">Belum ada komentar untuk fitur ini.</p>
                            @endforelse
                        </div>

                        <form wire:submit="addComment">
                            <div class="mb-2">
                                <label for="feature-comment-input" class="form-label small fw-semibold">Tambah Komentar</label>
                                <textarea
                                    id="feature-comment-input"
                                    rows="2"
                                    wire:model="commentText"
                                    class="form-control @error('commentText') is-invalid @enderror"
                                    placeholder="Tulis catatan atau progres terbaru..."></textarea>
                                @error('commentText') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                    Kirim Komentar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    window.initTaskKanbanColumn = function (el, wire) {
        if (typeof Sortable === 'undefined') return;
        if (el._sortableInstance) {
            el._sortableInstance.destroy();
        }

        el._sortableInstance = new Sortable(el, {
            group: 'task-kanban',
            animation: 150,
            draggable: '.kanban-card',
            ghostClass: 'kanban-ghost',
            filter: '.dropdown, button, a',
            preventOnFilter: false,
            onEnd: function (evt) {
                const featureId = Number(evt.item.dataset.featureId);
                const fromStatus = evt.from.dataset.status;
                const toStatus = evt.to.dataset.status;
                const orderedIds = Array.from(evt.to.querySelectorAll('.kanban-card'))
                    .map(card => Number(card.dataset.featureId))
                    .filter(Boolean);

                if (!featureId || !toStatus) return;

                if (toStatus === 'gagal' && fromStatus !== 'gagal') {
                    const refNode = evt.from.children[evt.oldIndex] || null;
                    evt.from.insertBefore(evt.item, refNode);
                }

                const component = wire || (window.Livewire && Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id')));
                if (!component) return;
                component.moveFeature(featureId, toStatus, orderedIds);
            }
        });
    };
</script>
@endpush
