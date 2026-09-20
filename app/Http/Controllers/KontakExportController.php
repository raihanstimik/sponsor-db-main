<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kontak;
use App\Services\ExportKontakService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KontakExportController extends Controller
{
    public function __invoke(Request $request, ExportKontakService $exportService): Response
    {
        if (! $request->user()?->can('export', Kontak::class)) {
            abort(403, 'Tidak punya hak export');
        }

        return $exportService->export($request, $request->user());
    }
}

