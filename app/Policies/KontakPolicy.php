<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kontak;
use App\Models\User;
use App\Policies\Concerns\HandlesResourcePermissions;

class KontakPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kontak.view_any');
    }

    use HandlesResourcePermissions;

    public function view(User $user, Kontak $kontak): bool
    {
        return $user->can('kontak.view');
    }

    protected string $permissionPrefix = 'kontak';

    public function create(User $user): bool
    {
        return $user->can('kontak.create');
    }

    public function update(User $user, Kontak $kontak): bool
    {
        return $user->can('kontak.update');
    }

    public function delete(User $user, Kontak $kontak): bool
    {
        return $user->can('kontak.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('kontak.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('kontak.export');
    }

    public function import(User $user): bool
    {
        return $user->can('kontak.import');
    }
}
