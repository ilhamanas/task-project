<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $project->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPm();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || ($user->isPm() && $this->view($user, $project));
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || ($user->isPm() && $this->view($user, $project));
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
