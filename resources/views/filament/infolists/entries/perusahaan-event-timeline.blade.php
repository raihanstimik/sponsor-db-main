@php
    /** @var \App\Models\Perusahaan $record */
    $record = $getRecord();
    $keaktifan = $record->keaktifan_sponsor;
    $analitik = $record->analitik_sponsor;
    $riwayats = $record->riwayatSponsorships()->with('kegiatan.kategoriKegiatan')->get();

    if ($riwayats->isEmpty()) {
        $fallbackKegiatans = $record->kegiatanPernahDiikuti();
        if ($fallbackKegiatans->isNotEmpty()) {
            $riwayats = $fallbackKegiatans->map(function ($keg) {
                $yr = $keg->tanggal_mulai ? (int) $keg->tanggal_mulai->format('Y') : null;
                if (! $yr && preg_match('/\b(20\d{2})\b/', $keg->nama_event, $m)) {
                    $yr = (int) $m[1];
                }

                return (object) [
                    'nama_event' => $keg->nama_event,
                    'tahun_efektif' => $yr,
                    'tanggal_partisipasi' => $keg->tanggal_mulai,
                    'kegiatan_id' => $keg->id,
                    'kegiatan' => $keg,
                    'paket' => null,
                    'nominal' => 0.0,
                    'bentuk_partisipasi' => 'Relasi Kontak PIC',
                    'catatan' => null,
                ];
            });
        }
    }

    $currentYear = (int) date('Y');
    $threeYearsAgo = $currentYear - 2;

    $trendTahunan = $analitik['trend_tahunan'] ?? [];
    $maxNominal = 0;
    foreach ($trendTahunan as $tData) {
        if ($tData['nominal'] > $maxNominal) {
            $maxNominal = $tData['nominal'];
        }
    }
@endphp

<div x-data="{ 
    filterMode: 'all', 
    hoveredYear: null,
    hoveredData: null 
}" class="space-y-5">

    {{-- ============================================================ --}}
    {{-- 1. EXECUTIVE DECISION SUPPORT: STATS & POTENTIAL MATRIX      --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Status Keaktifan -->
        <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Status Keaktifan
            </span>
            <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $keaktifan['badge_class'] }}">
                    {{ $keaktifan['label'] }}
                </span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                @if($keaktifan['tahun_terakhir'])
                    Terakhir aktif: <strong>{{ $keaktifan['tahun_terakhir'] }}</strong> ({{ $keaktifan['selisih_tahun'] == 0 ? 'Tahun ini' : $keaktifan['selisih_tahun'] . ' thn lalu' }})
                @else
                    Belum ada rekaman riwayat
                @endif
            </p>
        </div>

        <!-- Potensi Sponsor (Tier) -->
        <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Potensi Sponsor
            </span>
            <div class="mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-[#18225E]/10 dark:bg-indigo-500/20 text-[#18225E] dark:text-indigo-300 border border-[#18225E]/20 dark:border-indigo-500/30">
                    {{ $analitik['kategori_potensi'] }}
                </span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                Paket favorit: <strong>{{ $analitik['paket_favorit'] ?? '-' }}</strong>
            </p>
        </div>

        <!-- Total Investasi Dana -->
        <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Total Kontribusi Dana
            </span>
            <div class="mt-1 text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                {{ $analitik['total_investasi'] > 0 ? 'Rp ' . number_format($analitik['total_investasi'], 0, ',', '.') : '-' }}
            </div>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                Dari total <strong>{{ $analitik['total_event'] }}</strong> partisipasi event
            </p>
        </div>

        <!-- Rata-rata Investasi per Event -->
        <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Rata-Rata per Event
            </span>
            <div class="mt-1 text-base font-extrabold text-slate-900 dark:text-slate-100 font-mono">
                {{ $analitik['rata_rata_nominal'] > 0 ? 'Rp ' . number_format($analitik['rata_rata_nominal'], 0, ',', '.') : '-' }}
            </div>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                Baseline nilai proposal
            </p>
        </div>
    </div>


    {{-- ============================================================ --}}
    {{-- 3. VISUAL CHART: TREN DUKUNGAN SPONSOR PER TAHUN             --}}
    {{-- ============================================================ --}}
    @if(!empty($trendTahunan))
        <div class="rounded-xl border border-slate-200/80 bg-white p-4.5 dark:border-slate-800 dark:bg-slate-900 shadow-2xs">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3 dark:border-slate-800">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                        <x-filament::icon alias="heroicon-m-chart-bar" class="h-4 w-4 text-[#18225E] dark:text-blue-400" />
                        Grafik Tren Partisipasi &amp; Kontribusi Finansial per Tahun
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Arahkan kursor (*hover*) pada kolom tahun untuk melihat rincian nominal dan paket yang diambil
                    </p>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-sm bg-[#18225E] dark:bg-indigo-500"></span>
                        Investasi Dana (Rp)
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        Partisipasi Event
                    </span>
                </div>
            </div>

            <!-- SVG & Interactive Bar Representation -->
            <div class="mt-5">
                <div class="grid gap-3" style="grid-template-columns: repeat({{ max(count($trendTahunan), 1) }}, minmax(0, 1fr));">
                    @foreach($trendTahunan as $year => $d)
                        @php
                            $heightPercent = $maxNominal > 0 ? max(round(($d['nominal'] / $maxNominal) * 100), 12) : 35;
                            $hasInvestment = $d['nominal'] > 0;
                            $isRecentYear = $year >= $threeYearsAgo;
                        @endphp
                        <div 
                            class="flex flex-col items-center group cursor-pointer"
                            @mouseenter="hoveredYear = '{{ $year }}'; hoveredData = { nominal: '{{ number_format($d['nominal'], 0, ',', '.') }}', count: {{ $d['count'] }}, pakets: '{{ implode(', ', $d['pakets']) }}' }"
                            @mouseleave="hoveredYear = null; hoveredData = null"
                        >
                            <!-- Nominal Tooltip Value on top -->
                            <div class="h-6 flex items-center justify-center text-[11px] font-mono font-bold text-slate-700 dark:text-slate-300">
                                @if($hasInvestment)
                                    <span class="truncate text-[10px]">Rp {{ number_format($d['nominal'] / 1000000, 1, ',', '.') }}Jt</span>
                                @else
                                    <span class="text-[10px] text-slate-400">{{ $d['count'] }} ev</span>
                                @endif
                            </div>

                            <!-- Bar Column Container -->
                            <div class="relative w-full max-w-[48px] h-36 bg-slate-100 dark:bg-slate-800/80 rounded-t-lg flex flex-col justify-end p-1 transition-all group-hover:ring-2 group-hover:ring-[#18225E]/40 dark:group-hover:ring-indigo-400">
                                <!-- Filled Bar -->
                                <div 
                                    class="w-full rounded-t-md transition-all duration-300 {{ $isRecentYear ? 'bg-[#18225E] dark:bg-indigo-600' : 'bg-slate-400 dark:bg-slate-600' }} group-hover:bg-emerald-600 dark:group-hover:bg-emerald-500"
                                    style="height: {{ $heightPercent }}%;"
                                ></div>

                                <!-- Count Circle Badge -->
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-white dark:bg-slate-900 text-[9px] font-bold text-slate-700 dark:text-slate-300 shadow-2xs border border-slate-200 dark:border-slate-700">
                                    {{ $d['count'] }}
                                </span>
                            </div>

                            <!-- Year Label -->
                            <div class="mt-2 text-center">
                                <span class="text-xs font-bold {{ $isRecentYear ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $year }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Hover Details Bar -->
                <div class="mt-4 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 text-xs min-h-[40px] flex items-center justify-between">
                    <template x-if="hoveredYear">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="'Tahun ' + hoveredYear + ':'"></span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold" x-text="'Rp ' + hoveredData.nominal"></span>
                            <span class="text-slate-500 dark:text-slate-400" x-text="'• ' + hoveredData.count + ' Event diikuti'"></span>
                            <template x-if="hoveredData.pakets">
                                <span class="text-indigo-600 dark:text-indigo-400" x-text="'• Paket: ' + hoveredData.pakets"></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="!hoveredYear">
                        <span class="text-slate-400 dark:text-slate-500 italic">
                            Arahkan kursor pada diagram bar di atas untuk melihat rincian tahunan spesifik.
                        </span>
                    </template>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- 4. CHRONOLOGICAL TIMELINE: LATEST YEAR TO OLDEST             --}}
    {{-- ============================================================ --}}
    <div class="space-y-3">
        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-2.5 dark:border-slate-800">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    Histori Partisipasi Event (Diurutkan dari Tahun Terkini)
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Menampilkan {{ $riwayats->count() }} data keikutsertaan event kongres, simposium, &amp; sponsorship
                </p>
            </div>

            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 text-xs dark:bg-slate-800">
                <button
                    type="button"
                    @click="filterMode = 'all'"
                    :class="filterMode === 'all' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-2.5 py-1 rounded-md transition-all cursor-pointer text-xs"
                >
                    Semua ({{ $riwayats->count() }})
                </button>
                <button
                    type="button"
                    @click="filterMode = 'recent'"
                    :class="filterMode === 'recent' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-2.5 py-1 rounded-md transition-all cursor-pointer text-xs flex items-center gap-1"
                >
                    <span>3 Thn Terakhir</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                </button>
                <button
                    type="button"
                    @click="filterMode = 'with_nominal'"
                    :class="filterMode === 'with_nominal' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-2.5 py-1 rounded-md transition-all cursor-pointer text-xs flex items-center gap-1"
                >
                    <span>Hanya Bernominal</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                </button>
            </div>
        </div>

        @if($riwayats->isEmpty())
            <div class="py-10 text-center rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-6 bg-slate-50/50 dark:bg-slate-900/30">
                <x-filament::icon alias="heroicon-o-chart-bar" class="mx-auto h-8 w-8 text-slate-400" />
                <h5 class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Belum Ada Riwayat Partisipasi</h5>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Perusahaan ini belum memiliki catatan event sponsorship. Anda dapat menambahkan riwayat langsung melalui tab <strong>Riwayat Sponsorship</strong>.
                </p>
            </div>
        @else
            <!-- Timeline Item Cards -->
            <div class="relative pl-6 space-y-3.5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                @foreach($riwayats as $item)
                    @php
                        $yr = $item->tahun_efektif;
                        $isRecent = $yr && $yr >= $threeYearsAgo;
                        $hasNominal = $item->nominal > 0;
                        $paket = $item->paket;
                        $bentuk = $item->bentuk_partisipasi;
                        $catatan = $item->catatan;

                        $paketBadgeStyle = match(mb_strtolower((string)$paket)) {
                            'platinum' => 'bg-indigo-900 text-white dark:bg-indigo-100 dark:text-indigo-950 font-bold border-indigo-700',
                            'gold' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 font-bold border-amber-400',
                            'silver' => 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200 font-semibold border-slate-300',
                            'bronze' => 'bg-orange-100 text-orange-900 dark:bg-orange-950/60 dark:text-orange-300 font-semibold border-orange-400',
                            'simposium' => 'bg-purple-100 text-purple-900 dark:bg-purple-950/60 dark:text-purple-300 border-purple-300',
                            'booth' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300',
                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300',
                        };
                    @endphp

                    <div
                        x-show="
                            filterMode === 'all' || 
                            (filterMode === 'recent' && {{ $isRecent ? 'true' : 'false' }}) ||
                            (filterMode === 'with_nominal' && {{ $hasNominal ? 'true' : 'false' }})
                        "
                        x-transition
                        class="relative group"
                    >
                        <!-- Timeline Point Bullet -->
                        <span class="absolute -left-6 top-3.5 h-2.5 w-2.5 rounded-full ring-4 ring-white dark:ring-slate-900 {{ $isRecent ? 'bg-[#18225E] dark:bg-indigo-400' : 'bg-slate-400 dark:bg-slate-600' }}"></span>

                        <div class="rounded-xl border border-slate-200/80 bg-white p-4 transition-all hover:border-[#18225E]/40 hover:shadow-xs dark:border-slate-800 dark:bg-slate-900/90 dark:hover:border-slate-700">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <!-- Event Title & Details -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @php
                                            $itemKegiatanId = $item->kegiatan_id ?? $item->kegiatan?->id ?? null;
                                        @endphp
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                            @if(!empty($itemKegiatanId))
                                                <a
                                                    href="{{ \App\Filament\Resources\Kegiatans\KegiatanResource::getUrl('view', ['record' => $itemKegiatanId]) }}"
                                                    class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline inline-flex items-center gap-1.5 transition-colors group/title"
                                                    title="Tinjau Master Kegiatan"
                                                >
                                                    <span>{{ $item->nama_event }}</span>
                                                    <x-filament::icon alias="heroicon-m-arrow-top-right-on-square" class="w-3.5 h-3.5 text-slate-400 group-hover/title:text-blue-600 dark:group-hover/title:text-blue-400 shrink-0" />
                                                </a>
                                            @else
                                                {{ $item->nama_event }}
                                            @endif
                                        </h4>

                                        @if($yr)
                                            <span class="px-2 py-0.5 rounded-md text-xs font-bold font-mono {{ $isRecent ? 'bg-[#18225E] text-white dark:bg-indigo-600' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                                {{ $yr }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Sub-details: date, category, venue -->
                                    <div class="flex items-center gap-2 mt-1.5 flex-wrap text-xs text-slate-500 dark:text-slate-400">
                                        @if($item->tanggal_partisipasi)
                                            <span class="font-medium">📅 {{ $item->tanggal_partisipasi->format('d M Y') }}</span>
                                        @elseif($item->kegiatan?->tanggal_mulai)
                                            <span class="font-medium">📅 {{ $item->kegiatan->tanggal_mulai->format('d M Y') }}</span>
                                        @endif

                                        @if($item->kegiatan?->kategoriKegiatan)
                                            <span>&bull;</span>
                                            <span class="inline-flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                                <span class="h-2 w-2 rounded-full inline-block shrink-0" style="background-color: {{ $item->kegiatan->warna_efektif }}"></span>
                                                {{ $item->kegiatan->kategoriKegiatan->nama_kategori }}
                                            </span>
                                        @endif

                                        @if($item->kegiatan?->venue)
                                            <span>&bull;</span>
                                            <span class="truncate">📍 {{ $item->kegiatan->venue }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Details: Package & Nominal -->
                                <div class="text-left sm:text-right shrink-0 space-y-1">
                                    <div>
                                        @if($paket)
                                            <span class="inline-block px-2.5 py-0.5 rounded-md text-xs border {{ $paketBadgeStyle }}">
                                                {{ $paket }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400 italic">
                                                -
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        @if($hasNominal)
                                            <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                                                Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 dark:text-slate-500 font-mono">
                                                -
                                            </p>
                                        @endif
                                    </div>

                                    @if($bentuk)
                                        <p class="text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                            🎪 {{ $bentuk }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Catatan / Keterangan Lainnya -->
                            @if(filled($catatan))
                                <div class="mt-3 text-xs p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-300 flex items-start gap-2">
                                    <span class="shrink-0 text-slate-400 font-bold">💬 Keterangan:</span>
                                    <span class="leading-relaxed">{{ $catatan }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
