@php($data = \App\Filament\Widgets\DistribusiKategoriWidget::data())
<x-filament-widgets::widget>
<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-950/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded bg-primary-950 dark:bg-primary-400"></span>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Distribusi Event per Kategori Medis</h2>
            </div>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-gray-400">Pemetaan kegiatan kongres menurut spesialisasi kedokteran</p>
        </div>
        <span class="shrink-0 rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 dark:bg-white/5 dark:text-gray-300">{{ $data['total'] }} Total Event</span>
    </div>

    @if ($data['rows'] === [])
        <p class="py-6 text-center text-sm text-slate-500 dark:text-gray-400">Belum ada kategori kegiatan.</p>
    @else
        <div class="flex flex-col gap-3.5">
            @foreach ($data['rows'] as $row)
                <div class="group flex cursor-default flex-col gap-1">
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <span class="flex min-w-0 items-center gap-2 text-slate-800 dark:text-gray-100">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $row['hex'] }}"></span>
                            <span class="truncate" title="{{ $row['nama'] }}">{{ $row['nama'] }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2">
                            <span class="text-xs text-slate-500 dark:text-gray-400">{{ number_format($row['share'], 1, ',', '.') }}%</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-bold" style="color: {{ $row['hex'] }}; background-color: {{ $row['hex'] }}1A">{{ $row['count'] }} Event</span>
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200/70 dark:bg-white/10">
                        <div class="h-full rounded-full transition-all duration-500 group-hover:opacity-90" style="width: {{ $row['width'] }}%; background-color: {{ $row['hex'] }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-2 rounded-lg bg-slate-100/70 p-2 text-sm dark:bg-white/5">
        <span class="text-slate-500 dark:text-gray-400">Klik kategori untuk menyaring data kontak</span>
        <a href="{{ \App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource::getUrl() }}" wire:navigate class="shrink-0 font-semibold text-primary-800 hover:underline dark:text-primary-300">Kelola Kategori →</a>
    </div>
</div>
</x-filament-widgets::widget>
