<?php

use App\Http\Controllers\KontakExportController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['web', Authenticate::class, 'throttle:exports'])
    ->get('/admin/kontak/export', KontakExportController::class)
    ->name('kontaks.export');

Route::middleware(['web', Authenticate::class])
    ->get('/admin/impersonate/leave', function () {
        $manager = app(\Lab404\Impersonate\Services\ImpersonateManager::class);

        if ($manager->isImpersonating()) {
            $manager->leave();
        }

        return redirect('/admin');
    })
    ->name('impersonate.leave');

