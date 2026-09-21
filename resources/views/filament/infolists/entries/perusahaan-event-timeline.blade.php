@php
    $events = $getRecord()->kegiatanPernahDiikuti();
@endphp

@if($events->isEmpty())
    <div class="py-6 text-center text-sm text-slate-400 dark:text-slate-500">
        Belum ada riwayat partisipasi event kongres yang tercatat.
    </div>
@else
    <div class="relative pl-6 space-y-3 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
        @foreach($events as $event)
            <div class="relative group">
                <span class="absolute -left-6 top-3 w-2.5 h-2.5 rounded-full ring-4 ring-white dark:ring-slate-900 bg-[#18225E] dark:bg-blue-400"></span>
                <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-3 hover:bg-slate-100/70 dark:hover:bg-slate-800 transition-colors">
                    <div class="min-w-0">
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                            {{ $event->nama_event }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1 flex-wrap text-xs text-slate-500 dark:text-slate-400">
                            @if($event->kategoriKegiatan)
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                                    <span class="w-2 h-2 rounded-full inline-block shrink-0" style="background-color: {{ $event->warna_efektif }}"></span>
                                    {{ $event->kategoriKegiatan->nama_kategori }}
                                </span>
                            @else
                                <span class="font-medium text-slate-600 dark:text-slate-400">Umum</span>
                            @endif

                            @if($event->venue)
                                <span>&bull;</span>
                                <span class="truncate">📍 {{ $event->venue }}</span>
                            @endif

                            @if($event->tanggal_mulai)
                                <span>&bull;</span>
                                <span>📅 {{ $event->tanggal_mulai->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($event->durasi_hari)
                        <span class="shrink-0 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                            {{ $event->durasi_hari }} Hari
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
