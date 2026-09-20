<?php

declare(strict_types=1);

namespace App\Actions\Kontak;

use App\Services\ExportKontakService;
use App\Services\KontakSmartSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportKontakCsvAction
{
    public function __construct(
        protected KontakSmartSearch $smartSearch,
        protected ?ExportKontakService $exportService = null
    ) {
        $this->exportService = $exportService ?? app(ExportKontakService::class);
    }

    /**
     * Membangun query Kontak dengan filter dari request dan mengekspor hasilnya
     * sebagai file CSV streaming dengan chunk 500 baris.
     */
    public function execute(Request $request): StreamedResponse
    {
        $response = $this->exportService->export($request, $request->user());

        if ($response instanceof StreamedResponse) {
            return $response;
        }

        // Fallback jika format default bukan CSV
        $query = $this->buildQuery($request);

        return $this->exportService->exportCsv($query, (array) $request->input('columns', []));
    }

    /**
     * Membangun kueri Kontak beserta relasi dan filternya.
     */
    public function buildQuery(Request $request): Builder
    {
        return $this->exportService->buildQuery($request, (string) $request->input('scope', 'filtered'));
    }
}

