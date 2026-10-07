@php
    $data = $this->data;
    $selected = $this->selectedPerusahaan;
@endphp

<x-filament-widgets::widget>
    <x-dashboard-card
        title="Top 10 Sponsor Paling Aktif"
        subtitle="Sponsor paling loyal dan kontributor terbesar dalam kongres"
        :badge="'<div class=\'leading-tight text-center font-bold text-emerald-600 dark:text-emerald-400\'>' . $data['totalSponsor'] . ' Sponsor<br><span class=\'font-normal text-[10px] text-emerald-500\'>Terdata</span></div>'"
        badge-class="bg-emerald-50/80 dark:bg-emerald-950/50 border border-emerald-100 dark:border-emerald-900/40 px-3.5 py-1 text-xs"
        :footer-text="'Peringkat loyalitas berdasarkan keikutsertaan event & komitmen sponsorship'"
        :footer-url="\App\Filament\Resources\Perusahaans\PerusahaanResource::getUrl()"
        footer-label="Buka Direktori Sponsor &rarr;"
        footer-action-class="text-emerald-600 hover:underline dark:text-emerald-400 font-semibold"
    >
        @if ($data['rows'] === [])
            <div class="py-8 text-center text-sm text-slate-400 dark:text-gray-500">
                Belum ada data sponsor.
            </div>
        @else
            <div class="space-y-2">
                @foreach ($data['rows'] as $row)
                    <div
                        wire:click="selectPerusahaan({{ $row['id'] }})"
                        role="button"
                        tabindex="0"
                        class="group flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-100/80 dark:hover:bg-slate-800/70 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Rank badge -->
                            <div class="flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold shrink-0 {{ $row['rank'] === 1 ? 'bg-amber-400 text-slate-950 shadow-xs' : ($row['rank'] <= 3 ? 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400') }}">
                                {{ $row['rank'] }}
                            </div>

                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-lg bg-[#18225E] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $row['inisial'] }}
                            </div>

                            <!-- Title & Industry -->
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                                    {{ $row['nama'] }}
                                </h4>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                    <span>{{ $row['industri'] }}</span>
                                    <span>&bull;</span>
                                    <span class="text-slate-400 dark:text-slate-500">Update {{ $row['updated_text'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Stats & Drilldown Hint -->
                        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900/40">
                                    {{ $row['event_count'] }} Event
                                </span>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $row['pic_count'] }} PIC
                                </div>
                            </div>

                            <div class="p-1 rounded-md text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-dashboard-card>

    <!-- Slide-over Drill-down Modal (Zero Page Reload) -->
    @if($selected)
        <div
            x-data
            x-trap.inert="true"
            @keydown.escape.window="$wire.closeDetail()"
            class="fixed inset-0 z-50 overflow-hidden"
            role="dialog"
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div
                wire:click="closeDetail"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300"
            ></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-xl bg-white dark:bg-slate-900 shadow-2xl flex flex-col border-l border-slate-200 dark:border-slate-800">
                    <!-- Drawer Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3 bg-slate-50/70 dark:bg-slate-900/80">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-[#18225E] text-white flex items-center justify-center text-base font-bold shrink-0 tracking-wider shadow-sm">
                                {{ $selected->inisial }}
                            </div>
                            <div class="min-w-0">
                                <a
                                    href="{{ \App\Filament\Resources\Perusahaans\PerusahaanResource::getUrl('view', ['record' => $selected->id]) }}"
                                    class="text-base sm:text-lg font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 hover:underline inline-flex items-center gap-1.5 truncate group"
                                    title="Lihat Profil Perusahaan Lengkap"
                                >
                                    <span class="truncate">{{ $selected->nama_standar }}</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <div class="flex items-center gap-2 mt-1 flex-wrap text-xs">
                                    <span class="px-2 py-0.5 rounded-md font-medium bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $selected->industri ?? 'Sektor Umum' }}
                                    </span>
                                    <span class="text-amber-600 dark:text-amber-400 font-medium">
                                        🕒 Update: {{ $selected->updated_at ? $selected->updated_at->diffForHumans() : '-' }}
                                        {{ $selected->updatedBy ? '(' . $selected->updatedBy->name . ')' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeDetail"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Drawer Body (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-6">
                        <!-- Riwayat Partisipasi Kongres (Historical Tracking) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Rekam Jejak Sponsorship &amp; Event
                                </h4>
                                <span class="text-[11px] text-slate-400">Klik item untuk ditinjau</span>
                            </div>

                            @php
                                $riwayat = $selected->riwayatSponsorships;
                                if ($riwayat->isEmpty()) {
                                    $riwayat = $selected->kegiatans->sortByDesc(fn ($k) => $k->pivot?->tahun ?? ($k->tanggal_mulai ? $k->tanggal_mulai->format('Y') : 0));
                                }
                            @endphp

                            @if($riwayat->isEmpty())
                                <div class="py-4 text-center text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                                    Belum ada catatan riwayat kegiatan.
                                </div>
                            @else
                                <div class="space-y-2.5">
                                    @foreach($riwayat as $ev)
                                        @php
                                            $isModel = $ev instanceof \App\Models\PerusahaanKegiatan;
                                            $namaEvent = $isModel ? $ev->nama_event : $ev->nama_event;
                                            $thn = $isModel ? $ev->tahun_efektif : ($ev->pivot?->tahun ?? ($ev->tanggal_mulai ? $ev->tanggal_mulai->format('Y') : null));
                                            $pkt = $isModel ? $ev->paket : $ev->pivot?->paket;
                                            $nom = (float) ($isModel ? $ev->nominal : ($ev->pivot?->nominal ?? 0));
                                            $kategori = $isModel ? $ev->kegiatan?->kategoriKegiatan : $ev->kategoriKegiatan;
                                            $kegiatanMasterId = $isModel ? $ev->kegiatan_id : $ev->id;
                                        @endphp
                                        <div
                                            @if($isModel)
                                                wire:click="selectRiwayat({{ $ev->id }})"
                                            @else
                                                wire:click="selectKegiatanPivot({{ $ev->id }})"
                                            @endif
                                            role="button"
                                            tabindex="0"
                                            class="group/card p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:bg-slate-50/80 dark:hover:bg-slate-800 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-xs transition-all duration-150 cursor-pointer space-y-2"
                                        >
                                            <div class="flex items-center justify-between gap-3">
                                                <h5 class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white group-hover/card:text-blue-600 dark:group-hover/card:text-blue-400 transition-colors truncate">
                                                    {{ $namaEvent }}
                                                </h5>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    @if($thn)
                                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#18225E] text-white font-mono">
                                                            {{ $thn }}
                                                        </span>
                                                    @endif
                                                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover/card:text-blue-500 group-hover/card:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between gap-2 text-xs flex-wrap">
                                                <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                                    @if($kategori)
                                                        <span>{{ $kategori->nama_kategori }}</span>
                                                    @else
                                                        <span class="text-slate-400 italic">Sektor Umum</span>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    @if($pkt)
                                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                                            {{ $pkt }}
                                                        </span>
                                                    @endif
                                                    @if($nom > 0)
                                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-xs font-mono">
                                                            Rp {{ number_format($nom, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Hint & Action Affordance -->
                                            <div class="pt-1.5 mt-1 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                                                <span class="inline-flex items-center gap-1 font-medium text-blue-600 dark:text-blue-400 group-hover/card:underline">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Tinjau Riwayat</span>
                                                </span>

                                                @if($kegiatanMasterId)
                                                    <a
                                                        href="{{ \App\Filament\Resources\Kegiatans\KegiatanResource::getUrl('view', ['record' => $kegiatanMasterId]) }}"
                                                        target="_blank"
                                                        @click.stop
                                                        title="Buka master kegiatan di tab baru"
                                                        class="inline-flex items-center gap-1 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors"
                                                    >
                                                        <span>Event Master</span>
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Daftar PIC Kontak -->
                        <div class="space-y-3 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Daftar PIC Terhubung ({{ $selected->kontaks->count() }})
                            </h4>

                            @if($selected->kontaks->isEmpty())
                                <div class="py-4 text-center text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                                    Belum ada PIC kontak terdaftar.
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($selected->kontaks as $pic)
                                        <div class="p-2.5 rounded-xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-800/60 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white truncate">
                                                    {{ $pic->nama }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                                    {{ $pic->no_telepon ?? 'Tanpa Telepon' }}
                                                </p>
                                            </div>

                                            @if($pic->no_telepon)
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $pic->no_telepon);
                                                    if (str_starts_with($cleanPhone, '0')) {
                                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                                    }
                                                @endphp
                                                <a
                                                    href="https://wa.me/{{ $cleanPhone }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white transition-colors"
                                                >
                                                    <span>WhatsApp</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Drawer Footer -->
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900 flex justify-end">
                        <button
                            type="button"
                            wire:click="closeDetail"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            Tutup Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Tinjau Riwayat Sponsorship (Drill-down Inspection Dialog) -->
    @php
        $activeDetail = $this->selectedRiwayat;
        $activePivot = $this->selectedKegiatanPivot;
        $isDetailOpen = (bool) ($activeDetail || $activePivot);

        $dtNamaEvent = $activeDetail ? $activeDetail->nama_event : ($activePivot ? $activePivot->nama_event : '');
        $dtTahun = $activeDetail ? $activeDetail->tahun_efektif : ($activePivot?->pivot?->tahun ?? ($activePivot?->tanggal_mulai ? $activePivot->tanggal_mulai->format('Y') : ''));
        $dtPaket = $activeDetail ? $activeDetail->paket : ($activePivot?->pivot?->paket ?? null);
        $dtNominal = (float) ($activeDetail ? $activeDetail->nominal : ($activePivot?->pivot?->nominal ?? 0));
        $dtBentuk = $activeDetail ? $activeDetail->bentuk_partisipasi : ($activePivot?->pivot?->bentuk_partisipasi ?? null);
        $dtCatatan = $activeDetail ? $activeDetail->catatan : ($activePivot?->pivot?->catatan ?? null);
        $dtTanggal = $activeDetail ? $activeDetail->tanggal_partisipasi : null;
        $masterKegiatan = $activeDetail?->kegiatan ?? $activePivot;
        $dtKategori = $masterKegiatan?->kategoriKegiatan?->nama_kategori;
        $sponsorName = $selected ? $selected->nama_standar : ($activeDetail?->perusahaan?->nama_standar ?? '');
        $sponsorId = $selected ? $selected->id : $activeDetail?->perusahaan_id;
    @endphp

    @if($isDetailOpen)
        <div
            x-data
            x-trap.inert="true"
            @keydown.escape.window="$wire.closeRiwayatDetail()"
            class="fixed inset-0 z-[60] overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div
                wire:click="closeRiwayatDetail"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-200"
            ></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 sm:p-6 pointer-events-none">
                <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 pointer-events-auto flex flex-col max-h-[90vh] overflow-hidden transform transition-all">
                    <!-- Modal Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3 bg-slate-50/80 dark:bg-slate-900/90">
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-blue-100 text-blue-900 dark:bg-blue-950 dark:text-blue-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Tinjau Riwayat Sponsorship
                            </span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-1.5 leading-snug">
                                {{ $dtNamaEvent }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                                @if($dtKategori)
                                    <span class="font-medium text-slate-700 dark:text-slate-300">🏷️ {{ $dtKategori }}</span>
                                    <span>&bull;</span>
                                @endif
                                @if($dtTahun)
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">📅 Tahun {{ $dtTahun }}</span>
                                @endif
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeRiwayatDetail"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs">
                        <!-- Kartu Nilai Sponsorship -->
                        <div class="p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/50 bg-gradient-to-br from-indigo-50/50 via-white to-blue-50/30 dark:from-indigo-950/30 dark:via-slate-900 dark:to-slate-900 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Paket &amp; Nilai Kontribusi
                                </span>
                                @if($dtPaket)
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                        {{ $dtPaket }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Tanpa Paket</span>
                                @endif
                            </div>

                            <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                                {{ $dtNominal > 0 ? 'Rp ' . number_format($dtNominal, 0, ',', '.') : 'Rp 0 (In-Kind / Non-Nominal)' }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px]">
                                <div>
                                    <span class="text-slate-400 block">Sponsor:</span>
                                    <strong class="text-slate-800 dark:text-slate-200">{{ $sponsorName }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Tanggal Partisipasi:</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ $dtTanggal ? $dtTanggal->format('d F Y') : ($dtTahun ? "Tahun $dtTahun" : '-') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bentuk Partisipasi & Catatan -->
                        <div class="space-y-2">
                            @if($dtBentuk)
                                <div class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-850">
                                    <span class="text-[11px] font-bold text-slate-400 block mb-0.5">🎪 Bentuk Partisipasi:</span>
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ $dtBentuk }}</p>
                                </div>
                            @endif

                            @if($dtCatatan)
                                <div class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-850">
                                    <span class="text-[11px] font-bold text-slate-400 block mb-0.5">💬 Catatan &amp; Perjanjian:</span>
                                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $dtCatatan }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Data Master Kegiatan Kongres (Jika ada) -->
                        @if($masterKegiatan)
                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-850/60 space-y-2.5">
                                <h5 class="font-bold text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Informasi Event Kongres
                                </h5>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                    @if($masterKegiatan->venue)
                                        <div>
                                            <span class="text-slate-400 block">📍 Lokasi / Venue:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $masterKegiatan->venue }}</span>
                                        </div>
                                    @endif

                                    @if($masterKegiatan->penyelenggara)
                                        <div>
                                            <span class="text-slate-400 block">🏢 Penyelenggara:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $masterKegiatan->penyelenggara }}</span>
                                        </div>
                                    @endif

                                    @if($masterKegiatan->tanggal_mulai)
                                        <div class="sm:col-span-2">
                                            <span class="text-slate-400 block">📅 Jadwal Pelaksanaan:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $masterKegiatan->jadwal_dan_durasi }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-3.5 sm:p-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/90 flex flex-wrap items-center justify-between gap-2">
                        <button
                            type="button"
                            wire:click="closeRiwayatDetail"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            @if($masterKegiatan && $masterKegiatan->id)
                                <a
                                    href="{{ \App\Filament\Resources\Kegiatans\KegiatanResource::getUrl('view', ['record' => $masterKegiatan->id]) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#18225E] hover:bg-[#232f7a] text-white transition-colors"
                                >
                                    <span>Detail Master Kegiatan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif

                            @if($sponsorId)
                                <a
                                    href="{{ \App\Filament\Resources\Perusahaans\PerusahaanResource::getUrl('view', ['record' => $sponsorId]) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors"
                                >
                                    <span>Profil Perusahaan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
