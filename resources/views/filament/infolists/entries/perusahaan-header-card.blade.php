@php
    $record = $getRecord();
    $keaktifan = $record->keaktifan_sponsor;
    $totalPic = $record->kontaks()->count();
    $totalEvent = $record->riwayatSponsorships()->count();
    if ($totalEvent === 0) {
        $totalEvent = $record->kegiatans()->count();
    }

    $colorStyles = match($keaktifan['color'] ?? 'gray') {
        'success' => [
            'bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
            'dot' => 'bg-emerald-500',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
            'dot' => 'bg-amber-500',
        ],
        'info', 'primary' => [
            'bg' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
            'dot' => 'bg-blue-500',
        ],
        'danger' => [
            'bg' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
            'dot' => 'bg-rose-500',
        ],
        default => [
            'bg' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            'dot' => 'bg-slate-400',
        ],
    };
@endphp

<div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-start gap-3.5 sm:gap-4 transition-all">
    <!-- Avatar / Initial -->
    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-[#18225E] text-white flex items-center justify-center font-bold text-base sm:text-lg shrink-0 shadow-xs tracking-wider">
        {{ $record->inisial }}
    </div>

    <!-- Company Info & Pills -->
    <div class="min-w-0 flex-1 space-y-2">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-snug break-words">
                {{ $record->nama_standar }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                {{ $record->industri ?? 'Sektor Umum' }}
            </p>
        </div>

        <!-- Metric Badges (Natural Wrap, Anti-Clipped) -->
        <div class="flex flex-wrap items-center gap-2 pt-0.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $colorStyles['bg'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $colorStyles['dot'] }}"></span>
                <span>{{ $keaktifan['label'] }}</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200/70 dark:border-slate-700">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>{{ $totalPic }} PIC Terhubung</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/70 dark:border-blue-900/40">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ $totalEvent }} Event Diikuti</span>
            </span>
        </div>

        @if($record->updated_at)
            <div class="text-[11px] text-slate-400 dark:text-slate-500 pt-1 flex items-center gap-1.5 flex-wrap">
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Diperbarui {{ $record->updated_at->diffForHumans() }}</span>
                @if($record->updatedBy)
                    <span>&bull; oleh <strong class="font-semibold text-slate-600 dark:text-slate-300">{{ $record->updatedBy->name }}</strong></span>
                @endif
            </div>
        @endif
    </div>
</div>

