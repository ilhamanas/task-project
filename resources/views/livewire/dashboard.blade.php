<div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Dashboard</h1>
            <p class="text-secondary mb-0 small">Ringkasan status project dan progres fitur pada project yang Anda ikuti.</p>
        </div>
        @can('create', \App\Models\Project::class)
            <a href="{{ route('projects.index') }}" class="btn btn-primary fw-semibold">
                Kelola Project
            </a>
        @endcan
    </div>

    {{-- 4 Kartu Total Project per Status (Gaya Dasher UI) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-neutral p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold small text-dark">Planning</span>
                    <span class="icon-shape bg-secondary-subtle text-secondary" style="width: 38px; height: 38px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                            <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                            <path d="M9 12h6" />
                            <path d="M9 16h6" />
                        </svg>
                    </span>
                </div>
                <div class="fs-2 fw-bold lh-1 mb-2">{{ $projectStatusCounts['planning'] }}</div>
                <div class="small text-secondary">
                    {{ $totalProjects > 0 ? round(($projectStatusCounts['planning'] / $totalProjects) * 100) : 0 }}% dari total project
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-info p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold small text-dark">Berjalan</span>
                    <span class="icon-shape bg-info-subtle-ds" style="width: 38px; height: 38px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11" />
                        </svg>
                    </span>
                </div>
                <div class="fs-2 fw-bold lh-1 mb-2">{{ $projectStatusCounts['berjalan'] }}</div>
                <div class="small text-secondary">
                    {{ $totalProjects > 0 ? round(($projectStatusCounts['berjalan'] / $totalProjects) * 100) : 0 }}% dari total project
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-success p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold small text-dark">Selesai</span>
                    <span class="icon-shape bg-success-subtle-ds" style="width: 38px; height: 38px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                            <path d="M9 12l2 2l4 -4" />
                        </svg>
                    </span>
                </div>
                <div class="fs-2 fw-bold lh-1 mb-2">{{ $projectStatusCounts['selesai'] }}</div>
                <div class="small text-secondary">
                    {{ $totalProjects > 0 ? round(($projectStatusCounts['selesai'] / $totalProjects) * 100) : 0 }}% dari total project
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card-dasher bg-gradient-warning p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold small text-dark">Ditunda</span>
                    <span class="icon-shape bg-warning-subtle-ds" style="width: 38px; height: 38px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                            <path d="M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                        </svg>
                    </span>
                </div>
                <div class="fs-2 fw-bold lh-1 mb-2">{{ $projectStatusCounts['ditunda'] }}</div>
                <div class="small text-secondary">
                    {{ $totalProjects > 0 ? round(($projectStatusCounts['ditunda'] / $totalProjects) * 100) : 0 }}% dari total project
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Utama: Daftar Project (Kiri) + Ringkasan Progres (Kanan) --}}
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-dasher overflow-hidden">
                <div class="p-3 p-lg-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h2 class="h6 fw-bold mb-0">Daftar Project</h2>
                    <div class="ds-filter-pills" role="group" aria-label="Filter project dashboard">
                        <button
                            type="button"
                            wire:click="$set('projectFilter', 'semua')"
                            class="btn btn-sm {{ $projectFilter === 'semua' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                            Semua ({{ $totalProjects }})
                        </button>
                        <button
                            type="button"
                            wire:click="$set('projectFilter', 'berjalan')"
                            class="btn btn-sm {{ $projectFilter === 'berjalan' ? 'btn-primary fw-semibold' : 'btn-light border' }}">
                            Berjalan ({{ $runningProjectsCount }})
                        </button>
                        <button
                            type="button"
                            wire:click="$set('projectFilter', 'terlambat')"
                            class="btn btn-sm {{ $projectFilter === 'terlambat' ? 'btn-danger fw-semibold' : 'btn-light border' }}">
                            Terlambat ({{ $overdueProjectsCount }})
                        </button>
                    </div>
                </div>

                <div class="table-responsive-cards">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Project</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th style="min-width: 150px;">Progress</th>
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
                                <tr wire:key="dash-project-{{ $project->id }}">
                                    <td class="ps-3 table-cell-primary">
                                        <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-decoration-none text-dark">
                                            {{ $project->nama }}
                                        </a>
                                        <div class="text-secondary small">
                                            PIC: {{ $project->pic?->nama ?? '-' }}
                                        </div>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge {{ $statusBadge }}">{{ $project->statusLabel() }}</span>
                                    </td>
                                    <td class="small" data-label="Deadline">
                                        @if($project->tgl_target)
                                            <div class="d-inline-flex flex-wrap align-items-center gap-1">
                                                <span>{{ $project->tgl_target->format('d M Y') }}</span>
                                                @if($project->isOverdue())
                                                    <span class="badge bg-danger-subtle-ds">Terlambat</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td data-label="Progress">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 7px;">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small fw-semibold">{{ $progress }}%</span>
                                            </div>
                                            <div class="text-secondary" style="font-size: 0.75rem;">
                                                {{ $project->selesai_features_count }}/{{ $project->features_count }} fitur selesai
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">
                                        Tidak ada project pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top text-center" style="border-top-style: dashed !important;">
                    <a href="{{ route('projects.index') }}" class="text-decoration-none fw-semibold small text-primary-ds">
                        Lihat Semua Project
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-dasher p-3 p-lg-4">
                <h2 class="h6 fw-bold mb-3">Progres Keseluruhan</h2>
                <div class="d-flex align-items-baseline justify-content-between mb-2">
                    <div class="fs-1 fw-bold">{{ $overallProgress }}%</div>
                    <div class="small text-secondary">
                        {{ $statusCounts['selesai'] }} / {{ $totalFeatures }} fitur selesai
                    </div>
                </div>

                @php
                    $pctBelum = $totalFeatures > 0 ? round(($statusCounts['belum_mulai'] / $totalFeatures) * 100, 1) : 0;
                    $pctKerja = $totalFeatures > 0 ? round(($statusCounts['dikerjakan'] / $totalFeatures) * 100, 1) : 0;
                    $pctSelesai = $totalFeatures > 0 ? round(($statusCounts['selesai'] / $totalFeatures) * 100, 1) : 0;
                    $pctGagal = $totalFeatures > 0 ? round(($statusCounts['gagal'] / $totalFeatures) * 100, 1) : 0;
                @endphp
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $pctSelesai }}%;" title="Selesai"></div>
                    <div class="progress-bar bg-info" style="width: {{ $pctKerja }}%;" title="Dikerjakan"></div>
                    <div class="progress-bar bg-warning" style="width: {{ $pctBelum }}%;" title="Belum Mulai"></div>
                    <div class="progress-bar bg-danger" style="width: {{ $pctGagal }}%;" title="Gagal"></div>
                </div>

                <div class="p-3 rounded-3" style="background-color: var(--ds-gray-100);">
                    <div class="dashboard-mini-stats text-center">
                        <div class="bg-white border rounded-3 p-2 h-100">
                            <div class="fs-4 fw-bold">{{ $totalProjects }}</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">Total Project</div>
                        </div>
                        <div class="bg-white border rounded-3 p-2 h-100">
                            <div class="fs-4 fw-bold text-info">{{ $runningProjectsCount }}</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">Berjalan</div>
                        </div>
                        <div class="bg-white border rounded-3 p-2 h-100">
                            <div class="fs-4 fw-bold text-danger">{{ $overdueProjectsCount }}</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">Terlambat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
