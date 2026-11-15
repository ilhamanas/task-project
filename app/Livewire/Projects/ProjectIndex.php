<?php

namespace App\Livewire\Projects;

use App\Models\Feature;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Daftar Project')]
class ProjectIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $filterStatus = '';

    public bool $showFormModal = false;
    public bool $showMembersModal = false;
    public ?int $editingId = null;
    public ?int $managingProjectId = null;
    public string $managingProjectName = '';

    public string $nama = '';
    public string $deskripsi = '';
    public string $status = Project::STATUS_PLANNING;
    public string $pic_id = '';
    public string $tgl_mulai = '';
    public string $tgl_target = '';
    public array $selectedMembers = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Project::class);
        $this->resetForm();
        $this->pic_id = (string) auth()->id();
        $this->selectedMembers = [auth()->id()];
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        $project = Project::with('members:id')->findOrFail($id);
        $this->authorize('update', $project);

        $this->editingId = $project->id;
        $this->nama = $project->nama;
        $this->deskripsi = $project->deskripsi ?? '';
        $this->status = $project->status;
        $this->pic_id = $project->pic_id ? (string) $project->pic_id : '';
        $this->tgl_mulai = $project->tgl_mulai?->format('Y-m-d') ?? '';
        $this->tgl_target = $project->tgl_target?->format('Y-m-d') ?? '';
        $this->selectedMembers = $project->members->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        if ($this->editingId) {
            $project = Project::findOrFail($this->editingId);
            $this->authorize('update', $project);
        } else {
            $this->authorize('create', Project::class);
            $project = null;
        }

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'pic_id' => ['nullable', 'exists:users,id'],
            'tgl_mulai' => ['nullable', 'date'],
            'tgl_target' => array_filter([
                'nullable',
                'date',
                $this->tgl_mulai !== '' ? 'after_or_equal:tgl_mulai' : null,
            ]),
            'selectedMembers' => ['array'],
            'selectedMembers.*' => ['integer', 'exists:users,id'],
        ], [
            'nama.required' => 'Nama project wajib diisi.',
            'status.in' => 'Status project tidak valid.',
            'pic_id.exists' => 'PIC tidak ditemukan.',
            'tgl_target.after_or_equal' => 'Tanggal target harus sama atau setelah tanggal mulai.',
        ]);

        $payload = [
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] !== '' ? $validated['deskripsi'] : null,
            'status' => $validated['status'],
            'pic_id' => $validated['pic_id'] !== '' ? (int) $validated['pic_id'] : null,
            'tgl_mulai' => $validated['tgl_mulai'] !== '' ? $validated['tgl_mulai'] : null,
            'tgl_target' => $validated['tgl_target'] !== '' ? $validated['tgl_target'] : null,
        ];

        $memberIds = collect($validated['selectedMembers'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->when($payload['pic_id'], fn ($c) => $c->push($payload['pic_id']))
            ->when(! auth()->user()->isAdmin(), fn ($c) => $c->push(auth()->id()))
            ->unique()
            ->values()
            ->all();

        if ($project) {
            $project->update($payload);
            $project->members()->sync($memberIds);
            session()->flash('success', 'Project berhasil diperbarui.');
        } else {
            $created = Project::create($payload);
            $created->members()->sync($memberIds);
            session()->flash('success', 'Project baru berhasil dibuat.');
        }

        $this->closeFormModal();
    }

    public function openMembers(int $id): void
    {
        $project = Project::with('members:id')->findOrFail($id);
        $this->authorize('manageMembers', $project);

        $this->managingProjectId = $project->id;
        $this->managingProjectName = $project->nama;
        $this->selectedMembers = $project->members->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->showMembersModal = true;
    }

    public function closeMembersModal(): void
    {
        $this->showMembersModal = false;
        $this->managingProjectId = null;
        $this->managingProjectName = '';
        $this->selectedMembers = [];
    }

    public function saveMembers(): void
    {
        $project = Project::findOrFail($this->managingProjectId);
        $this->authorize('manageMembers', $project);

        $validated = $this->validate([
            'selectedMembers' => ['array'],
            'selectedMembers.*' => ['integer', 'exists:users,id'],
        ]);

        $memberIds = collect($validated['selectedMembers'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->when(! auth()->user()->isAdmin(), fn ($c) => $c->push(auth()->id()))
            ->unique()
            ->values()
            ->all();

        $project->members()->sync($memberIds);
        session()->flash('success', "Anggota project \"{$project->nama}\" berhasil diperbarui.");
        $this->closeMembersModal();
    }

    public function delete(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);

        $name = $project->nama;
        $project->delete();
        session()->flash('success', "Project \"{$name}\" berhasil dihapus.");
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nama = '';
        $this->deskripsi = '';
        $this->status = Project::STATUS_PLANNING;
        $this->pic_id = '';
        $this->tgl_mulai = '';
        $this->tgl_target = '';
        $this->selectedMembers = [];
        $this->resetValidation();
    }

    public function render()
    {
        $user = auth()->user();

        $projects = Project::query()
            ->accessibleBy($user)
            ->with(['pic:id,nama'])
            ->withCount([
                'members',
                'features',
                'features as selesai_features_count' => fn ($q) => $q->where('status', Feature::STATUS_SELESAI),
            ])
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('nama', 'like', $term)
                      ->orWhere('deskripsi', 'like', $term);
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(10);

        $activeUsers = User::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'role']);

        return view('livewire.projects.project-index', [
            'projects' => $projects,
            'statuses' => Project::STATUSES,
            'activeUsers' => $activeUsers,
        ]);
    }
}
