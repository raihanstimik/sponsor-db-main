@php($data = \App\Filament\Widgets\DistribusiKategoriWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Distribusi Event per Kategori Medis"
        subtitle="Pemetaan kegiatan kongres menurut spesialisasi kedokteran"
        :badge="$data['total'].' Total Event'"
        badge-class="bg-slate-100 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300"
        footer-text="Klik kelola untuk konfigurasi taksonomi spesialisasi"
        :footer-url="\App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource::getUrl()"
        footer-label="Kelola Kategori &rarr;"
        footer-action-class="text-[#18225E] hover:underline dark:text-sky-400"
    >
        @if ($data['rows'] === [])
            <div class="py-8 text-center text-sm text-slate-400 dark:text-gray-500">
                Belum ada kategori kegiatan.
            </div>
        @else
            <div class="flex flex-col gap-2">
                @foreach ($data['rows'] as $row)
                    <div class="flex flex-col gap-1 p-1.5 rounded-lg transition-colors duration-150 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $row['hex'] }}"></span>
                                <span class="truncate text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200" title="{{ $row['nama'] }}">
                                    {{ $row['nama'] }}
                                </span>
                            </div>
                            <div class="flex shrink-0 items-center gap-2.5">
                                <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                    {{ number_format($row['share'], 1, ',', '.') }}%
                                </span>
                                <span class="rounded px-1.5 py-0.5 text-xs font-semibold tabular-nums" style="color: {{ $row['hex'] }}; background-color: {{ $row['hex'] }}18">
                                    {{ $row['count'] }} Event
                                </span>
                            </div>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full transition-all duration-300" 
                                 style="width: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>
</x-filament-widgets::widget>