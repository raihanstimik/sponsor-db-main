@php($data = \App\Filament\Widgets\DistribusiKategoriWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Distribusi Event per Kategori Medis"
        subtitle="Pemetaan kegiatan kongres menurut spesialisasi kedokteran"
        :badge="$data['total'].' Total Event'"
        badge-class="bg-slate-100 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300 px-3.5 py-1 text-xs"
        footer-text="Klik tautan untuk penyesuaian metadata taksonomi"
        :footer-url="\App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource::getUrl()"
        footer-label="Kelola Kategori &rarr;"
        footer-action-class="text-blue-600 hover:underline dark:text-sky-400 font-medium"
    >
        @if ($data['rows'] === [])
            <div class="py-8 text-center text-sm text-slate-400 dark:text-gray-500">
                Belum ada kategori kegiatan.
            </div>
        @else
            <div class="flex flex-col gap-2.5 sm:gap-3">
                @foreach ($data['rows'] as $row)
                    <div class="flex items-center gap-2.5 sm:gap-3 py-0.5 transition-colors duration-150">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $row['hex'] }}"></span>
                        
                        <span class="truncate text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 flex-1 min-w-0" title="{{ $row['nama'] }}">
                            {{ $row['nama'] }}
                        </span>
                        
                        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                            <div class="h-1.5 sm:h-2 w-14 sm:w-20 md:w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 shrink-0">
                                <div class="h-full rounded-full transition-all duration-300" 
                                     style="width: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}"></div>
                            </div>
                            
                            <span class="w-9 sm:w-11 text-right text-xs text-slate-400 dark:text-slate-400 tabular-nums shrink-0 font-normal">
                                {{ number_format($row['share'], 1, '.', '') }}%
                            </span>
                            
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold tabular-nums shrink-0 text-center min-w-[56px]" 
                                  style="color: {{ $row['hex'] }}; background-color: {{ $row['hex'] }}18">
                                {{ $row['count'] }} Event
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>
</x-filament-widgets::widget>