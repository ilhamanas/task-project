<?php

namespace App\Livewire;

use App\Models\Feature;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $projectFilter = 'semua';

    public function render()
    {
        $user = auth()->user();

        $accessibleProjectIds = Project::query()
            ->accessibleBy($user)
            ->select('id');

        $featureCountsRaw = Feature::query()
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            Feature::STATUS_BELUM_MULAI => (int) ($featureCountsRaw[Feature::STATUS_BELUM_MULAI] ?? 0),
            Feature::STATUS_DIKERJAKAN => (int) ($featureCountsRaw[Feature::STATUS_DIKERJAKAN] ?? 0),
            Feature::STATUS_SELESAI => (int) ($featureCountsRaw[Feature::STATUS_SELESAI] ?? 0),
            Feature::STATUS_GAGAL => (int) ($featureCountsRaw[Feature::STATUS_GAGAL] ?? 0),
        ];

        $totalFeatures = array_sum($statusCounts);
        $overallProgress = $totalFeatures > 0
            ? (int) round(($statusCounts[Feature::STATUS_SELESAI] / $totalFeatures) * 100)
            : 0;

        $allProjects = Project::query()
            ->accessibleBy($user)
            ->with(['pic:id,nama'])
            ->withCount([
                'features',
                'features as selesai_features_count' => fn ($q) => $q->where('status', Feature::STATUS_SELESAI),
            ])
            ->orderByRaw('CASE WHEN tgl_target IS NULL THEN 1 ELSE 0 END, tgl_target ASC')
            ->get();

        $totalProjects = $allProjects->count();
        $projectStatusCounts = [
            Project::STATUS_PLANNING => $allProjects->where('status', Project::STATUS_PLANNING)->count(),
            Project::STATUS_BERJALAN => $allProjects->where('status', Project::STATUS_BERJALAN)->count(),
            Project::STATUS_SELESAI => $allProjects->where('status', Project::STATUS_SELESAI)->count(),
            Project::STATUS_DITUNDA => $allProjects->where('status', Project::STATUS_DITUNDA)->count(),
        ];
        $runningProjectsCount = $projectStatusCounts[Project::STATUS_BERJALAN];
        $overdueProjectsCount = $allProjects->filter(fn (Project $project) => $project->isOverdue())->count();

        $projects = match ($this->projectFilter) {
            'planning' => $allProjects->where('status', Project::STATUS_PLANNING)->values(),
            'berjalan' => $allProjects->where('status', Project::STATUS_BERJALAN)->values(),
            'selesai' => $allProjects->where('status', Project::STATUS_SELESAI)->values(),
            'ditunda' => $allProjects->where('status', Project::STATUS_DITUNDA)->values(),
            'terlambat' => $allProjects->filter(fn (Project $project) => $project->isOverdue())->values(),
            default => $allProjects,
        };

        return view('livewire.dashboard', [
            'projectStatusCounts' => $projectStatusCounts,
            'statusCounts' => $statusCounts,
            'totalFeatures' => $totalFeatures,
            'overallProgress' => $overallProgress,
            'totalProjects' => $totalProjects,
            'runningProjectsCount' => $runningProjectsCount,
            'overdueProjectsCount' => $overdueProjectsCount,
            'projects' => $projects,
        ]);
    }
}
