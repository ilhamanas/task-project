<?php

namespace App\Livewire\Projects;

use App\Models\Feature;
use App\Models\FeatureComment;
use App\Models\FeatureLog;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Detail Project')]
class ProjectShow extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public Project $project;

    #[Url(as: 'tab')]
    public string $viewMode = 'kanban';

    public string $filterStatus = '';
    public string $filterPriority = '';
    public string $searchFeature = '';

    // Modal tambah / ubah fitur
    public bool $showFeatureModal = false;
    public ?int $editingFeatureId = null;
    public string $nama = '';
    public string $deskripsi = '';
    public string $prioritas = Feature::PRIORITY_SEDANG;
    public string $pic_id = '';
    public string $tgl_target = '';

    // Modal alasan gagal
    public bool $showFailModal = false;
    public ?int $failingFeatureId = null;
    public string $failingFeatureName = '';
    public string $catatan_gagal = '';
    public array $pendingFailOrder = [];

    // Modal detail & komentar fitur
    public bool $showDetailModal = false;
    public ?int $detailFeatureId = null;
    public string $commentText = '';
    public $attachmentFile = null;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        $this->project = $project;

        if (! in_array($this->viewMode, ['kanban', 'tabel', 'backlog', 'riwayat'], true)) {
            $this->viewMode = 'kanban';
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['kanban', 'tabel', 'backlog', 'riwayat'], true) ? $mode : 'kanban';
        $this->resetPage();
    }

    public function updatingSearchFeature(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPriority(): void
    {
        $this->resetPage();
    }

    public function openCreateFeature(): void
    {
        $this->authorize('view', $this->project);
        $this->resetFeatureForm();
        $this->showFeatureModal = true;
    }

    public function openEditFeature(int $featureId): void
    {
        $feature = $this->findProjectFeature($featureId);

        $this->editingFeatureId = $feature->id;
        $this->nama = $feature->nama;
        $this->deskripsi = $feature->deskripsi ?? '';
        $this->prioritas = $feature->prioritas;
        $this->pic_id = $feature->pic_id ? (string) $feature->pic_id : '';
        $this->tgl_target = $feature->tgl_target?->format('Y-m-d') ?? '';
        $this->resetValidation();
        $this->showFeatureModal = true;
    }

    public function closeFeatureModal(): void
    {
        $this->showFeatureModal = false;
        $this->resetFeatureForm();
    }

    public function saveFeature(): void
    {
        $this->authorize('view', $this->project);

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'prioritas' => ['required', Rule::in(array_keys(Feature::PRIORITIES))],
            'pic_id' => ['nullable', 'exists:users,id'],
            'tgl_target' => ['nullable', 'date'],
        ], [
            'nama.required' => 'Nama fitur wajib diisi.',
            'prioritas.in' => 'Prioritas tidak valid.',
            'pic_id.exists' => 'PIC tidak ditemukan.',
        ]);

        $payload = [
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] !== '' ? $validated['deskripsi'] : null,
            'prioritas' => $validated['prioritas'],
            'pic_id' => $validated['pic_id'] !== '' ? (int) $validated['pic_id'] : null,
            'tgl_target' => $validated['tgl_target'] !== '' ? $validated['tgl_target'] : null,
        ];

        if ($this->editingFeatureId) {
            $feature = $this->findProjectFeature($this->editingFeatureId);
            $feature->update($payload);
            session()->flash('success', 'Fitur berhasil diperbarui.');
        } else {
            DB::transaction(function () use ($payload) {
                $nextOrder = (int) $this->project->features()
                    ->where('status', Feature::STATUS_BELUM_MULAI)
                    ->max('urutan') + 1;

                $feature = $this->project->features()->create(array_merge($payload, [
                    'status' => Feature::STATUS_BELUM_MULAI,
                    'urutan' => $nextOrder,
                ]));

                FeatureLog::create([
                    'feature_id' => $feature->id,
                    'status_lama' => null,
                    'status_baru' => Feature::STATUS_BELUM_MULAI,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);
            });
            session()->flash('success', 'Fitur baru berhasil ditambahkan ke Belum Mulai.');
        }

        $this->closeFeatureModal();
    }

    public function requestStatusChange(int $featureId, string $newStatus): void
    {
        $this->moveFeature($featureId, $newStatus);
    }

    public function moveFeature(int $featureId, string $targetStatus, array $orderedIds = []): void
    {
        $feature = $this->findProjectFeature($featureId);

        if (! array_key_exists($targetStatus, Feature::STATUSES)) {
            return;
        }

        if ($targetStatus === Feature::STATUS_GAGAL && $feature->status !== Feature::STATUS_GAGAL) {
            $this->failingFeatureId = $feature->id;
            $this->failingFeatureName = $feature->nama;
            $this->catatan_gagal = '';
            $this->pendingFailOrder = $orderedIds;
            $this->resetValidation();
            $this->showFailModal = true;
            return;
        }

        $statusChanged = $feature->status !== $targetStatus;

        DB::transaction(function () use ($feature, $targetStatus, $orderedIds, $statusChanged) {
            if ($statusChanged) {
                $nextOrder = (int) $this->project->features()
                    ->where('status', $targetStatus)
                    ->max('urutan') + 1;

                $feature->transitionStatus($targetStatus, auth()->user(), null, $nextOrder);
            }

            $this->applyColumnOrder($targetStatus, $orderedIds);
        });

        if ($statusChanged) {
            session()->flash('success', "Status fitur \"{$feature->nama}\" diubah ke {$feature->fresh()->statusLabel()}.");
        }
    }

    public function closeFailModal(): void
    {
        $this->showFailModal = false;
        $this->failingFeatureId = null;
        $this->failingFeatureName = '';
        $this->catatan_gagal = '';
        $this->pendingFailOrder = [];
        $this->resetValidation();
    }

    public function confirmFailStatus(): void
    {
        $validated = $this->validate([
            'catatan_gagal' => ['required', 'string', 'min:3'],
        ], [
            'catatan_gagal.required' => 'Alasan gagal wajib diisi.',
            'catatan_gagal.min' => 'Alasan gagal minimal 3 karakter.',
        ]);

        $feature = $this->findProjectFeature((int) $this->failingFeatureId);

        DB::transaction(function () use ($feature, $validated) {
            $nextOrder = (int) $this->project->features()
                ->where('status', Feature::STATUS_GAGAL)
                ->max('urutan') + 1;

            $feature->transitionStatus(Feature::STATUS_GAGAL, auth()->user(), $validated['catatan_gagal'], $nextOrder);
            $this->applyColumnOrder(Feature::STATUS_GAGAL, $this->pendingFailOrder);
        });

        session()->flash('success', "Fitur \"{$feature->nama}\" dipindahkan ke Gagal.");
        $this->closeFailModal();
    }

    public function deleteFeature(int $featureId): void
    {
        $feature = $this->findProjectFeature($featureId);
        $name = $feature->nama;
        $feature->delete();
        if ($this->detailFeatureId === $featureId) {
            $this->closeDetailModal();
        }
        session()->flash('success', "Fitur \"{$name}\" berhasil dihapus.");
    }

    public function openDetailFeature(int $featureId): void
    {
        $feature = $this->findProjectFeature($featureId);
        $this->detailFeatureId = $feature->id;
        $this->commentText = '';
        $this->attachmentFile = null;
        $this->resetValidation();
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailFeatureId = null;
        $this->commentText = '';
        $this->attachmentFile = null;
        $this->resetValidation();
    }

    public function addComment(): void
    {
        $feature = $this->findProjectFeature((int) $this->detailFeatureId);

        $validated = $this->validate([
            'commentText' => ['required', 'string', 'max:2000'],
        ], [
            'commentText.required' => 'Komentar tidak boleh kosong.',
            'commentText.max' => 'Komentar maksimal 2000 karakter.',
        ]);

        $feature->comments()->create([
            'user_id' => auth()->id(),
            'isi' => trim($validated['commentText']),
        ]);

        $this->commentText = '';
        $this->resetValidation('commentText');
    }

    public function deleteComment(int $commentId): void
    {
        $feature = $this->findProjectFeature((int) $this->detailFeatureId);
        $comment = $feature->comments()->findOrFail($commentId);

        abort_unless(
            $comment->user_id === auth()->id() || auth()->user()->isAdmin(),
            403,
            'Anda tidak berhak menghapus komentar ini.'
        );

        $comment->delete();
    }

    public function uploadAttachment(): void
    {
        $feature = $this->findProjectFeature((int) $this->detailFeatureId);

        $this->validate([
            'attachmentFile' => [
                'required',
                'file',
                'max:5120',
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp,txt,zip',
            ],
        ], [
            'attachmentFile.required' => 'Pilih file terlebih dahulu.',
            'attachmentFile.max' => 'Ukuran file maksimal 5 MB.',
            'attachmentFile.mimes' => 'Format file yang didukung: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, webp, txt, zip.',
        ]);

        $path = $this->attachmentFile->store("feature-attachments/{$feature->id}", 'local');

        $feature->attachments()->create([
            'user_id' => auth()->id(),
            'nama_file' => $this->attachmentFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $this->attachmentFile->getMimeType(),
            'ukuran' => $this->attachmentFile->getSize() ?? 0,
        ]);

        $this->attachmentFile = null;
        $this->resetValidation('attachmentFile');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $feature = $this->findProjectFeature((int) $this->detailFeatureId);
        $attachment = $feature->attachments()->findOrFail($attachmentId);

        abort_unless(
            $attachment->user_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isPm(),
            403,
            'Anda tidak berhak menghapus lampiran ini.'
        );

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();
    }

    protected function applyColumnOrder(string $status, array $orderedIds): void
    {
        $cleanIds = collect($orderedIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($cleanIds->isEmpty()) {
            return;
        }

        $validIds = $this->project->features()
            ->where('status', $status)
            ->whereIn('id', $cleanIds)
            ->pluck('id')
            ->all();

        $position = 1;
        foreach ($cleanIds as $id) {
            if (in_array($id, $validIds, true)) {
                $this->project->features()
                    ->where('id', $id)
                    ->where('status', $status)
                    ->update(['urutan' => $position++]);
            }
        }
    }

    protected function findProjectFeature(int $featureId): Feature
    {
        $this->authorize('view', $this->project);

        return $this->project->features()->findOrFail($featureId);
    }

    protected function resetFeatureForm(): void
    {
        $this->editingFeatureId = null;
        $this->nama = '';
        $this->deskripsi = '';
        $this->prioritas = Feature::PRIORITY_SEDANG;
        $this->pic_id = '';
        $this->tgl_target = '';
        $this->resetValidation();
    }

    public function render()
    {
        $this->project->load(['pic:id,nama', 'members:id,nama,role']);

        $countsRaw = $this->project->features()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            Feature::STATUS_BELUM_MULAI => (int) ($countsRaw[Feature::STATUS_BELUM_MULAI] ?? 0),
            Feature::STATUS_DIKERJAKAN => (int) ($countsRaw[Feature::STATUS_DIKERJAKAN] ?? 0),
            Feature::STATUS_SELESAI => (int) ($countsRaw[Feature::STATUS_SELESAI] ?? 0),
            Feature::STATUS_GAGAL => (int) ($countsRaw[Feature::STATUS_GAGAL] ?? 0),
        ];

        $totalFeatures = array_sum($statusCounts);
        $progress = $totalFeatures > 0
            ? (int) round(($statusCounts[Feature::STATUS_SELESAI] / $totalFeatures) * 100)
            : 0;

        $allFiltered = $this->project->features()
            ->with('pic:id,nama')
            ->withCount(['comments', 'attachments'])
            ->when($this->searchFeature !== '', function ($q) {
                $term = '%' . $this->searchFeature . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('deskripsi', 'like', $term);
                });
            })
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $kanbanColumns = collect(Feature::STATUSES)->mapWithKeys(function ($label, $status) use ($allFiltered) {
            return [$status => $allFiltered->where('status', $status)->values()];
        });

        $tableFeatures = $this->filterStatus !== ''
            ? $allFiltered->where('status', $this->filterStatus)->values()
            : $allFiltered;

        $backlogFeatures = collect();
        if ($this->viewMode === 'backlog') {
            $backlogFeatures = $this->project->features()
                ->with('pic:id,nama')
                ->withCount(['comments', 'attachments'])
                ->where('status', Feature::STATUS_BELUM_MULAI)
                ->when($this->searchFeature !== '', function ($q) {
                    $term = '%' . $this->searchFeature . '%';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('nama', 'like', $term)
                            ->orWhere('deskripsi', 'like', $term);
                    });
                })
                ->when($this->filterPriority !== '', fn ($q) => $q->where('prioritas', $this->filterPriority))
                ->orderByRaw("CASE prioritas WHEN 'tinggi' THEN 1 WHEN 'sedang' THEN 2 WHEN 'rendah' THEN 3 ELSE 4 END")
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();
        }

        $featureLogs = null;
        if ($this->viewMode === 'riwayat') {
            $featureLogs = FeatureLog::query()
                ->whereHas('feature', fn ($q) => $q->where('project_id', $this->project->id))
                ->with(['feature:id,nama', 'user:id,nama'])
                ->when($this->searchFeature !== '', function ($q) {
                    $term = '%' . $this->searchFeature . '%';
                    $q->whereHas('feature', fn ($sub) => $sub->where('nama', 'like', $term));
                })
                ->latest('created_at')
                ->latest('id')
                ->paginate(20);
        }

        $detailFeature = null;
        if ($this->showDetailModal && $this->detailFeatureId) {
            $detailFeature = $this->project->features()
                ->with([
                    'pic:id,nama',
                    'comments.user:id,nama,role',
                    'attachments.user:id,nama',
                    'logs.user:id,nama',
                ])
                ->find($this->detailFeatureId);
        }

        return view('livewire.projects.project-show', [
            'statusCounts' => $statusCounts,
            'totalFeatures' => $totalFeatures,
            'progress' => $progress,
            'kanbanColumns' => $kanbanColumns,
            'features' => $tableFeatures,
            'backlogFeatures' => $backlogFeatures,
            'featureLogs' => $featureLogs,
            'detailFeature' => $detailFeature,
            'statuses' => Feature::STATUSES,
            'priorities' => Feature::PRIORITIES,
            'members' => $this->project->members,
        ]);
    }
}
