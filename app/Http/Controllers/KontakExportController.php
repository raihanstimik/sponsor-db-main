<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Kontak\ExportKontakCsvAction;
use App\Models\Kontak;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KontakExportController extends Controller
{
    public function __invoke(Request $request, ExportKontakCsvAction $action): StreamedResponse
    {
        if (! $request->user()?->can('export', Kontak::class)) {
            abort(403, 'Tidak punya hak export');
        }

        return $action->execute($request);
    }
}
