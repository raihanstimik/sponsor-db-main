@php($data = \App\Filament\Widgets\TopEventWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Top 5 Event Terbesar"
        subtitle="Berdasarkan akumulasi jumlah kontak PIC sponsor terdaftar"
        :badge="$data['totalTop'].' PIC Total Top 5'"
        badge-class="bg-orange-100 font-bold text-orange-800 dark:bg-orange-500/10 dark:text-orange-300"
        dot-class="bg-orange-500"
        :footer-text="'Dominasi event terbesar: '.number_format($data['dominasi'], 1, ',', '.').'% dari total kontak'"
        :footer-url="\App\Filament\Resources\Kegiatans\KegiatanResource::getUrl()"
        footer-label="Lihat Rincian Event →"
        footer-action-class="text-orange-700 hover:underline dark:text-orange-300"
    >
        @if ($data['rows'] === [])
            <p class="py-6 text-center text-sm text-slate-500 dark:text-gray-400">Belum ada kegiatan.</p>
        @else
            <div class="flex h-56 items-stretch justify-between gap-2 px-2 sm:gap-4">
                @foreach ($data['rows'] as $row)
                    <div class="flex min-h-0 min-w-0 flex-1 flex-col items-center">
                        <div class="flex min-h-0 w-full flex-1 flex-col items-center justify-end">
                            <div class="mb-1.5 shrink-0 text-sm font-bold text-slate-800 opacity-90 transition-transform group-hover:scale-110 dark:text-gray-100">{{ $row['count'] }}</div>
                            <div class="flex min-h-6 w-full max-w-12 items-start justify-center rounded-t-lg pt-1 transition-all duration-300" style="height: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}">
                                <span class="font-mono text-[10px] leading-4 text-white/80">#{{ $row['rank'] }}</span>
                            </div>
                        </div>
                        <div class="mt-2.5 w-full shrink-0 text-center">
                            <span class="block truncate text-sm font-bold text-slate-800 dark:text-gray-100" title="{{ $row['nama'] }}">{{ $row['nama_pendek'] }}</span>
                            <span class="block truncate text-[11px] text-slate-500 dark:text-gray-400" title="{{ $row['sub'] }}">{{ $row['sub'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>
</x-filament-widgets::widget>
