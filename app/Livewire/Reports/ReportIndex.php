<?php

namespace App\Livewire\Reports;

use App\Models\Feature;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Laporan Project')]
class ReportIndex extends Component
{
    public string $filterProject = '';
    public string $filterStatus = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function resetFilters(): void
    {
        $this->filterProject = '';
        $this->filterStatus = '';
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function exportCsv(): StreamedResponse
    {
        $projects = $this->filteredProjects();
        $filename = 'laporan-project-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($projects) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel membaca karakter dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama Project',
                'Status',
                'PIC',
                'Tanggal Mulai',
                'Tanggal Target',
                'Terlambat',
                'Belum Mulai',
                'Dikerjakan',
                'Selesai',
                'Gagal',
                'Total Fitur',
                'Progress (%)',
            ]);

            foreach ($projects as $project) {
                fputcsv($handle, [
                    $this->safeCsvCell($project->nama),
                    $project->statusLabel(),
                    $this->safeCsvCell($project->pic?->nama ?? '-'),
                    $project->tgl_mulai?->format('Y-m-d') ?? '-',
                    $project->tgl_target?->format('Y-m-d') ?? '-',
                    $project->isOverdue() ? 'Ya' : 'Tidak',
                    $project->belum_mulai_count,
                    $project->dikerjakan_count,
                    $project->selesai_features_count,
                    $project->gagal_count,
                    $project->features_count,
                    $project->progressPercent() . '%',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function safeCsvCell(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    protected function filteredProjects(): Collection
    {
        $user = auth()->user();

        return Project::query()
            ->accessibleBy($user)
            ->with(['pic:id,nama'])
            ->when($this->filterProject !== '', fn ($q) => $q->where('id', (int) $this->filterProject))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('tgl_target', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('tgl_target', '<=', $this->dateTo))
            ->withCount([
                'features',
                'features as belum_mulai_count' => fn ($q) => $q->where('status', Feature::STATUS_BELUM_MULAI),
                'features as dikerjakan_count' => fn ($q) => $q->where('status', Feature::STATUS_DIKERJAKAN),
                'features as selesai_features_count' => fn ($q) => $q->where('status', Feature::STATUS_SELESAI),
                'features as gagal_count' => fn ($q) => $q->where('status', Feature::STATUS_GAGAL),
            ])
            ->orderBy('nama')
            ->get();
    }

    public function render()
    {
        $user = auth()->user();
        $projects = $this->filteredProjects();

        $accessibleProjects = Project::query()
            ->accessibleBy($user)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $totalFitur = $projects->sum('features_count');
        $totalSelesai = $projects->sum('selesai_features_count');
        $totalGagal = $projects->sum('gagal_count');
        $totalTerlambat = $projects->filter(fn (Project $p) => $p->isOverdue())->count();
        $rataProgress = $totalFitur > 0 ? (int) round(($totalSelesai / $totalFitur) * 100) : 0;

        $failedFeatures = Feature::query()
            ->whereIn('project_id', $projects->pluck('id'))
            ->where('status', Feature::STATUS_GAGAL)
            ->with(['project:id,nama', 'pic:id,nama'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('livewire.reports.report-index', [
            'projects' => $projects,
            'accessibleProjects' => $accessibleProjects,
            'statuses' => Project::STATUSES,
            'totalProjects' => $projects->count(),
            'totalFitur' => $totalFitur,
            'totalSelesai' => $totalSelesai,
            'totalGagal' => $totalGagal,
            'totalTerlambat' => $totalTerlambat,
            'rataProgress' => $rataProgress,
            'failedFeatures' => $failedFeatures,
        ]);
    }
}
