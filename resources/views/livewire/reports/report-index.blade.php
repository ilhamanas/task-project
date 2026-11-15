<div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Laporan Project</h1>
            <p class="text-secondary mb-0 small">Rekap progres, status fitur, dan project terlambat berdasarkan hak akses Anda.</p>
        </div>
        <button type="button" wire:click="exportCsv" class="btn btn-primary fw-semibold">
            Unduh CSV
        </button>
    </div>

    {{-- Filter Laporan --}}
    <div class="card-dasher p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="report-project" class="form-label small fw-semibold mb-1">Project</label>
                <select id="report-project" wire:model.live="filterProject" class="form-select form-select-sm">
                    <option value="">Semua Project</option>
                    @foreach($accessibleProjects as $ap)
                        <option value="{{ $ap->id }}">{{ $ap->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="report-status" class="form-label small fw-semibold mb-1">Status Project</label>
                <select id="report-status" wire:model.live="filterStatus" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="report-from" class="form-label small fw-semibold mb-1">Target Dari</label>
                <input id="report-from" type="date" wire:model.live="dateFrom" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label for="report-to" class="form-label small fw-semibold mb-1">Target Sampai</label>
                <input id="report-to" type="date" wire:model.live="dateTo" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-3 text-md-end">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light border">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Ringkasan Angka --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-info p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Total Project</div>
                <div class="fs-2 fw-bold">{{ $totalProjects }}</div>
                <div class="small text-secondary">{{ $totalFitur }} total fitur</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-success p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Progress Keseluruhan</div>
                <div class="fs-2 fw-bold">{{ $rataProgress }}%</div>
                <div class="small text-secondary">{{ $totalSelesai }} fitur selesai</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-warning p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Project Terlambat</div>
                <div class="fs-2 fw-bold">{{ $totalTerlambat }}</div>
                <div class="small text-secondary">Melewati tanggal target</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-danger p-3 h-100">
                <div class="text-secondary small fw-semibold mb-1">Fitur Gagal</div>
                <div class="fs-2 fw-bold">{{ $totalGagal }}</div>
                <div class="small text-secondary">Perlu evaluasi / dibuka ulang</div>
            </div>
        </div>
    </div>

    {{-- Tabel Rekap Per Project --}}
    <div class="card-dasher overflow-hidden mb-4">
        <div class="p-3 border-bottom">
            <h2 class="h6 fw-bold mb-0">Rekap Progres per Project</h2>
        </div>
        <div class="table-responsive-cards">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Project</th>
                        <th>Status</th>
                        <th>PIC</th>
                        <th>Target</th>
                        <th class="text-center">Belum Mulai</th>
                        <th class="text-center">Dikerjakan</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Gagal</th>
                        <th style="min-width: 140px;">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php $progress = $project->progressPercent(); @endphp
                        <tr wire:key="report-project-{{ $project->id }}">
                            <td class="ps-3 table-cell-primary">
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ $project->nama }}
                                </a>
                            </td>
                            <td data-label="Status">
                                <span class="badge bg-light text-dark border">{{ $project->statusLabel() }}</span>
                            </td>
                            <td class="small" data-label="PIC">{{ $project->pic?->nama ?? '-' }}</td>
                            <td class="small" data-label="Target">
                                @if($project->tgl_target)
                                    <div class="d-inline-flex flex-wrap align-items-center gap-1">
                                        <span>{{ $project->tgl_target->format('d M Y') }}</span>
                                        @if($project->isOverdue())
                                            <span class="badge bg-danger-subtle-ds">Terlambat</span>
                                        @endif
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" data-label="Belum Mulai">{{ $project->belum_mulai_count }}</td>
                            <td class="text-center text-info fw-semibold" data-label="Dikerjakan">{{ $project->dikerjakan_count }}</td>
                            <td class="text-center text-primary-ds fw-semibold" data-label="Selesai">{{ $project->selesai_features_count }}</td>
                            <td class="text-center text-danger fw-semibold" data-label="Gagal">{{ $project->gagal_count }}</td>
                            <td data-label="Progress">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 7px;">
                                        <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $progress }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">
                                Tidak ada data project sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daftar Fitur Gagal (jika ada) --}}
    @if($failedFeatures->isNotEmpty())
        <div class="card-dasher overflow-hidden">
            <div class="p-3 border-bottom">
                <h2 class="h6 fw-bold mb-0">Daftar Fitur Gagal &amp; Catatan</h2>
            </div>
            <div class="table-responsive-cards">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Project</th>
                            <th>Fitur</th>
                            <th>PIC</th>
                            <th class="pe-3">Catatan Gagal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedFeatures as $ff)
                            <tr wire:key="failed-feature-{{ $ff->id }}">
                                <td class="ps-3 small fw-semibold table-cell-primary">{{ $ff->project?->nama ?? '-' }}</td>
                                <td class="small" data-label="Fitur">{{ $ff->nama }}</td>
                                <td class="small" data-label="PIC">{{ $ff->pic?->nama ?? '-' }}</td>
                                <td class="pe-3 small text-danger" data-label="Catatan Gagal">{{ $ff->catatan_gagal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
