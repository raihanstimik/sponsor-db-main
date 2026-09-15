@php($data = \App\Filament\Widgets\TopEventWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Top 5 Event Terbesar"
        subtitle="Akumulasi keterlibatan kontak PIC sponsor terdaftar"
        :badge="$data['totalTop'].' PIC Total Top 5'"
        badge-class="bg-orange-50 font-medium text-orange-900 border border-orange-200/60 dark:bg-orange-950/30 dark:text-orange-300 dark:border-orange-800/40"
        :footer-text="'Dominasi event terbesar: '.number_format($data['dominasi'], 1, ',', '.').'% dari total database'"
        :footer-url="\App\Filament\Resources\Kegiatans\KegiatanResource::getUrl()"
        footer-label="Lihat Rincian Event &rarr;"
        footer-action-class="text-[#18225E] hover:underline dark:text-sky-400"
    >
        @if ($data['rows'] === [])
            <div class="py-8 text-center text-sm text-slate-400 dark:text-gray-500">
                Belum ada kegiatan.
            </div>
        @else
            <div class="flex flex-col gap-2">
                @foreach ($data['rows'] as $row)
                    <div class="flex items-center gap-3 p-1.5 rounded-lg transition-colors duration-150 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded text-xs font-bold tabular-nums {{ $row['rank'] === 1 ? 'bg-[#18225E] text-white shadow-xs' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $row['rank'] }}
                        </span>
                        
                        <div class="flex min-w-0 flex-1 flex-col gap-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate text-xs sm:text-sm font-semibold text-slate-900 dark:text-white" title="{{ $row['nama'] }}">
                                    {{ $row['nama'] }}
                                </span>
                                <span class="shrink-0 text-xs font-bold text-slate-700 dark:text-slate-200 tabular-nums">
                                    {{ $row['count'] }} <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">PIC</span>
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full transition-all duration-300" 
                                         style="width: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}"></div>
                                </div>
                                <span class="truncate text-[10px] text-slate-500 dark:text-slate-400 max-w-[150px]" title="{{ $row['sub'] }}">
                                    {{ $row['sub'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>
</x-filament-widgets::widget>