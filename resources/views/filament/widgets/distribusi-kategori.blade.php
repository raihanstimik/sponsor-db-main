@php($data = \App\Filament\Widgets\DistribusiKategoriWidget::data())
<x-filament-widgets::widget>
    <x-dashboard-card
        title="Distribusi Event per Kategori Medis"
        subtitle="Pemetaan kegiatan kongres menurut spesialisasi kedokteran"
        :badge="$data['total'].' Total Event'"
        badge-class="bg-slate-100 font-semibold text-slate-600 dark:bg-white/5 dark:text-gray-300"
        dot-class="bg-primary-950 dark:bg-primary-400"
        footer-text="Klik kategori untuk menyaring data kontak"
        :footer-url="\App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource::getUrl()"
        footer-label="Kelola Kategori →"
        footer-action-class="text-primary-800 hover:underline dark:text-primary-300"
    >
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
    </x-dashboard-card>
</x-filament-widgets::widget>
