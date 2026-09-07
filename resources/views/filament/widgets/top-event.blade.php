@php($data = \App\Filament\Widgets\TopEventWidget::data())
<x-filament-widgets::widget>
<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-950/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded bg-orange-500"></span>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Top 5 Event Terbesar</h2>
            </div>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-gray-400">Berdasarkan akumulasi jumlah kontak PIC sponsor terdaftar</p>
        </div>
        <span class="shrink-0 rounded bg-orange-100 px-2 py-1 text-xs font-bold text-orange-800 dark:bg-orange-500/10 dark:text-orange-300">{{ $data['totalTop'] }} PIC Total Top 5</span>
    </div>

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

    <div class="mt-4 flex items-center justify-between gap-2 rounded-lg bg-slate-100/70 p-2 text-sm dark:bg-white/5">
        <span class="text-slate-500 dark:text-gray-400">Dominasi event terbesar: {{ number_format($data['dominasi'], 1, ',', '.') }}% dari total kontak</span>
        <a href="{{ \App\Filament\Resources\Kegiatans\KegiatanResource::getUrl() }}" wire:navigate class="shrink-0 font-semibold text-orange-700 hover:underline dark:text-orange-300">Lihat Rincian Event →</a>
    </div>
</div>
</x-filament-widgets::widget>
