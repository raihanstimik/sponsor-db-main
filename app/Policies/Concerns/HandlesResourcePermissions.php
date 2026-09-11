<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Trait untuk membakukan pengecekan izin CRUD Filament Resource Policy.
 * Model policies cukup menyetel properti $permissionPrefix.
 */
trait HandlesResourcePermissions
{
    protected function getPermissionPrefix(): string
    {
        return property_exists($this, 'permissionPrefix') ? $this->permissionPrefix : '';
    }

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.view_any");
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.create");
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.update");
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.delete");
    }

    public function deleteAny(User $user): bool
    {
        return $user->can("{$this->getPermissionPrefix()}.delete");
    }
}
