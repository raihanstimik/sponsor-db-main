<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdateUserProfileAction
{
    /**
     * Memperbarui profil pengguna (data diri, avatar, dan pergantian password).
     *
     * @param array{
     *     name: string,
     *     email: string,
     *     phone?: ?string,
     *     avatar_url?: ?string,
     *     current_password?: ?string,
     *     password?: ?string
     * } $data
     *
     * @throws ValidationException
     */
    public function execute(User $user, array $data): User
    {
        $newPassword = $data['password'] ?? null;
        if (filled($newPassword)) {
            $currentPassword = $data['current_password'] ?? '';
            if (! Hash::check((string) $currentPassword, $user->password)) {
                throw ValidationException::withMessages([
                    'data.current_password' => 'Password saat ini salah.',
                ]);
            }
        }

        $payload = [
            'name' => trim((string) $data['name']),
            'email' => trim((string) $data['email']),
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'avatar_url' => $data['avatar_url'] ?? null,
        ];

        if (filled($newPassword)) {
            $payload['password'] = Hash::make((string) $newPassword);
        }

        $oldAvatar = $user->avatar_url;
        $newAvatar = $payload['avatar_url'] ?? null;

        DB::transaction(function () use ($user, $payload): void {
            $user->update($payload);
        });

        // Manajemen pembersihan berkas avatar lama
        if ($newAvatar && $oldAvatar && $oldAvatar !== $newAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        } elseif ($newAvatar === null && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return $user->fresh();
    }
}
