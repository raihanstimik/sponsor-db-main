@php($data = \App\Filament\Widgets\TopEventWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Top 5 Event Terbesar"
        subtitle="Akumulasi keterlibatan kontak PIC sponsor"
        :badge="'<div class=\'leading-tight text-center font-bold text-blue-600 dark:text-blue-400\'>' . $data['totalTop'] . ' PIC<br><span class=\'font-normal text-[10px] text-blue-500\'>Total</span></div>'"
        badge-class="bg-blue-50/80 dark:bg-blue-950/50 border border-blue-100 dark:border-blue-900/40 px-3.5 py-1 text-xs"
        :footer-text="'Dominasi event: <span class=\'font-semibold text-slate-700 dark:text-slate-300\'>' . number_format($data['dominasi'], 1, ',', '.') . '%</span> total database'"
        :footer-url="\App\Filament\Resources\Kegiatans\KegiatanResource::getUrl()"
        footer-label="Lihat Rincian Event &rarr;"
        footer-action-class="text-blue-600 hover:underline dark:text-sky-400 font-semibold"
    >
        @if ($data['rows'] === [])
            <div class="py-8 text-center text-sm text-slate-400 dark:text-gray-500">
                Belum ada kegiatan.
            </div>
        @else
            <div class="flex flex-col gap-4 sm:gap-4.5">
                @foreach ($data['rows'] as $row)
                    <div class="group flex flex-col transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            @if ($row['rank'] === 1)
                                <span class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-lg text-xs sm:text-sm font-bold bg-[#0f172a] text-white shadow-xs dark:bg-slate-100 dark:text-slate-900">
                                    {{ $row['rank'] }}
                                </span>
                            @else
                                <span class="flex h-7 w-7 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-lg text-xs sm:text-sm font-bold text-white shadow-xs" style="background-color: {{ $row['hex'] }}">
                                    {{ $row['rank'] }}
                                </span>
                            @endif
                            
                            <div class="flex min-w-0 flex-1 flex-col justify-center">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs sm:text-sm font-bold text-slate-900 dark:text-white" title="{{ $row['nama'] }}">
                                        {{ $row['nama'] }}
                                    </span>
                                    <span class="shrink-0 text-xs sm:text-sm font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ number_format($row['count'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">PIC</span>
                                    </span>
                                </div>
                                <p class="truncate text-xs text-slate-400 dark:text-slate-400 mt-0.5" title="{{ $row['sub'] }}">
                                    {{ $row['sub'] }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="h-1.5 sm:h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 mt-2">
                            @if ($row['rank'] === 1)
                                <div class="h-full rounded-full transition-all duration-300 bg-[#0f172a] dark:bg-slate-200" 
                                     style="width: {{ $row['width'] }}%;"></div>
                            @else
                                <div class="h-full rounded-full transition-all duration-300" 
                                     style="width: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>
</x-filament-widgets::widget>