<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Divisi;
use App\Models\User;

class DivisiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Divisi $divisi): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Divisi $divisi): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Divisi $divisi): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        // Divisi default sistem tidak boleh dihapus
        if (strtolower($divisi->name) === 'umum' || strtolower($divisi->slug) === 'umum') {
            return false;
        }

        // Mencegah penghapusan jika masih ada user yang terhubung
        return $divisi->users()->count() === 0;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

