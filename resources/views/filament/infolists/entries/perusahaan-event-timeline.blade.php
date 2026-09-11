@php
    $events = $getRecord()->kegiatanPernahDiikuti();
@endphp

@if($events->isEmpty())
    <div class="py-4 text-center text-sm text-slate-400 dark:text-slate-500">
        Belum ada riwayat partisipasi event kongres yang tercatat.
    </div>
@else
    <div class="relative pl-6 space-y-3 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
        @foreach($events as $event)
            <div class="relative group">
                <span class="absolute -left-6 top-1.5 w-2.5 h-2.5 rounded-full ring-4 ring-white dark:ring-slate-900 {{ $loop->first ? 'bg-orange-500' : 'bg-slate-400' }}"></span>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $event->nama_event }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $event->kategoriKegiatan?->nama_kategori ?? 'Umum' }}
                            @if($event->tanggal_mulai)
                                &bull; {{ $event->tanggal_mulai->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                    @if($loop->first)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800 dark:bg-orange-950/40 dark:text-orange-400">
                            Terbaru
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
