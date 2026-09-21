@php
    $kontaks = $getRecord()->kontaksForDetailModal();
@endphp

@if($kontaks->isEmpty())
    <div class="py-4 text-center text-sm text-slate-400 dark:text-slate-500">
        Belum ada PIC kontak yang terdaftar untuk perusahaan ini.
    </div>
@else
    <div class="space-y-2">
        @foreach($kontaks as $kontak)
            @php
                $inisialPic = mb_strtoupper(mb_substr($kontak->nama, 0, 2));
                $waUrl = \App\Support\PhoneNormalizer::whatsappUrl($kontak->no_telepon);
            @endphp
            <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[#18225E] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                        {{ $inisialPic ?: 'PI' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                            {{ $kontak->nama ?: '(Tanpa Nama PIC)' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ $kontak->kegiatans->isNotEmpty() ? $kontak->kegiatans->pluck('nama_event')->implode(', ') : ($kontak->kegiatan?->nama_event ?? $kontak->kategoriKegiatan?->nama_kategori ?? 'Kongres Umum') }}
                            @if($kontak->email)
                                &bull; <a href="mailto:{{ $kontak->email }}" class="hover:underline text-slate-600 dark:text-slate-300">{{ $kontak->email }}</a>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($waUrl !== '#')
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium shadow-xs transition-colors" title="Hubungi via WhatsApp">
                            <x-heroicon-m-chat-bubble-left-right class="w-3.5 h-3.5" />
                            <span>{{ $kontak->no_telepon }}</span>
                        </a>
                    @elseif(filled($kontak->no_telepon))
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-mono">
                            {{ $kontak->no_telepon }}
                        </span>
                    @else
                        <span class="text-xs text-slate-400 italic">Tanpa nomor</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="pt-2 flex justify-end">
    <a href="{{ \App\Filament\Resources\Kontaks\KontakResource::getUrl() }}?tableFilters[cari][q]={{ urlencode($getRecord()->nama_standar) }}" 
       class="text-xs font-semibold text-[#18225E] dark:text-blue-400 hover:underline inline-flex items-center gap-1">
        <span>Buka & Kelola Seluruh PIC di Modul Kontak</span>
        <span>&rarr;</span>
    </a>
</div>

