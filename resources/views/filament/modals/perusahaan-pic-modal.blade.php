@php
    $kontaks = $record->kontaks()->with(['kegiatan', 'kategoriKegiatan'])->orderBy('nama')->get();
    $kontaks = $kontaks ?? $record->kontaksForDetailModal();
@endphp

<div class="space-y-3 p-1">
    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar PIC: {{ $record->nama_standar }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $kontaks->count() }} kontak terhubung ke perusahaan ini</p>
        </div>
        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-400">
            {{ $kontaks->count() }} PIC
        </span>
    </div>

    @if($kontaks->isEmpty())
        <div class="py-8 text-center text-sm text-slate-400 dark:text-slate-500">
            Belum ada PIC kontak yang terdaftar untuk perusahaan ini.
        </div>
    @else
        <div class="max-h-80 overflow-y-auto space-y-2 pr-1">
            @foreach($kontaks as $kontak)
                @php
                    $inisialPic = mb_strtoupper(mb_substr($kontak->nama, 0, 2));
                    $waUrl = \App\Support\PhoneNormalizer::whatsappUrl($kontak->no_telepon);
                @endphp
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#18225E] to-[#1E294B] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            {{ $inisialPic }}
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $kontak->nama }}</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $kontak->kegiatan?->nama_event ?? $kontak->kategoriKegiatan?->nama_kategori ?? 'Kongres Umum' }}
                                @if($kontak->email)
                                    &bull; {{ $kontak->email }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($waUrl !== '#')
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium shadow-sm transition-colors">
                                <span>WA: {{ $kontak->no_telepon }}</span>
                            </a>
                        @elseif(filled($kontak->no_telepon))
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $kontak->no_telepon }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">No phone</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="pt-2 flex justify-end">
        <a href="{{ \App\Filament\Resources\Kontaks\KontakResource::getUrl() }}?tableFilters[perusahaan][value]={{ $record->id }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            <span>Buka & Kelola di Modul Kontak</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
