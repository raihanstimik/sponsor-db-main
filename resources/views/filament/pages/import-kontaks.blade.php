<x-filament-panels::page>
    {{-- PROGRESS STEPPER --}}
    @php
        $currentStep = 1;
        if ($this->saved && $this->saveResult) {
            $currentStep = 3;
        } elseif ($this->previews || $this->rows) {
            $currentStep = 2;
        }
    @endphp

    <div class="mb-4 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Stepper Progress --}}
            <nav class="flex items-center gap-2 sm:gap-4 text-xs font-medium" aria-label="Progress">
                {{-- Step 1 --}}
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full font-mono text-[11px] font-semibold transition-all duration-200 {{ $currentStep > 1 ? 'bg-success-500 text-white' : ($currentStep === 1 ? 'bg-primary-600 text-white ring-2 ring-primary-600/30 ring-offset-1 dark:ring-offset-slate-900' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                        @if ($currentStep > 1)
                            <x-filament::icon alias="heroicon-m-check" class="h-3.5 w-3.5" />
                        @else
                            1
                        @endif
                    </span>
                    <span class="{{ $currentStep === 1 ? 'font-semibold text-slate-900 dark:text-white' : ($currentStep > 1 ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500') }}">
                        Unggah File
                    </span>
                </div>

                <div class="hidden h-0.5 w-8 rounded-full bg-slate-200 sm:block dark:bg-slate-700">
                    <div class="h-full bg-success-500 transition-all duration-300 {{ $currentStep > 1 ? 'w-full' : 'w-0' }}"></div>
                </div>

                {{-- Step 2 --}}
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full font-mono text-[11px] font-semibold transition-all duration-200 {{ $currentStep > 2 ? 'bg-success-500 text-white' : ($currentStep === 2 ? 'bg-primary-600 text-white ring-2 ring-primary-600/30 ring-offset-1 dark:ring-offset-slate-900' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                        @if ($currentStep > 2)
                            <x-filament::icon alias="heroicon-m-check" class="h-3.5 w-3.5" />
                        @else
                            2
                        @endif
                    </span>
                    <span class="{{ $currentStep === 2 ? 'font-semibold text-slate-900 dark:text-white' : ($currentStep > 2 ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500') }}">
                        Pratinjau &amp; Analisis
                    </span>
                </div>

                <div class="hidden h-0.5 w-8 rounded-full bg-slate-200 sm:block dark:bg-slate-700">
                    <div class="h-full bg-success-500 transition-all duration-300 {{ $currentStep > 2 ? 'w-full' : 'w-0' }}"></div>
                </div>

                {{-- Step 3 --}}
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full font-mono text-[11px] font-semibold transition-all duration-200 {{ $currentStep === 3 ? 'bg-success-600 text-white ring-2 ring-success-600/30 ring-offset-1 dark:ring-offset-slate-900' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                        @if ($currentStep === 3)
                            <x-filament::icon alias="heroicon-m-check" class="h-3.5 w-3.5" />
                        @else
                            3
                        @endif
                    </span>
                    <span class="{{ $currentStep === 3 ? 'font-semibold text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                        Selesai
                    </span>
                </div>
            </nav>

            {{-- Live Indicator / Info --}}
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full {{ $currentStep === 3 ? 'bg-success-500' : ($currentStep === 2 ? 'bg-primary-500 animate-pulse' : 'bg-slate-400') }}"></span>
                @if ($currentStep === 1)
                    <span>Siap menerima format Excel &amp; CSV</span>
                @elseif ($currentStep === 2)
                    <span>Analisis selesai &bull; verifikasi data sebelum simpan</span>
                @else
                    <span>Data berhasil disimpan ke database</span>
                @endif
            </div>
        </div>
    </div>

    @if ($this->saved && $this->saveResult)
        {{-- ============================================================ --}}
        {{-- STEP 3: HASIL IMPORT SELESAI                                  --}}
        {{-- ============================================================ --}}
        <div class="overflow-hidden rounded-2xl border border-success-500/30 bg-white shadow-sm dark:border-success-500/20 dark:bg-slate-900">
            <div class="bg-gradient-to-r from-success-500/10 via-success-500/5 to-transparent p-6 sm:p-8">
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-success-500 text-white shadow-md shadow-success-500/30">
                        <x-filament::icon alias="heroicon-o-check-badge" class="h-7 w-7" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white" style="font-family: var(--font-display);">
                            Import Kontak &amp; Perusahaan Berhasil
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Semua baris valid telah berhasil diproses, dinormalisasi, dan disimpan secara aman ke database.
                        </p>
                    </div>
                </div>

                {{-- Metric Highlights --}}
                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200/80 bg-white/80 p-4 backdrop-blur-xs dark:border-slate-800 dark:bg-slate-800/80">
                        <div class="text-xs font-semibold uppercase tracking-wider text-success-600 dark:text-success-400">
                            Kontak Baru Disimpan
                        </div>
                        <div class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                            {{ number_format($this->saveResult['kontak_dibuat']) }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Kontak siap digunakan di CRM
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white/80 p-4 backdrop-blur-xs dark:border-slate-800 dark:bg-slate-800/80">
                        <div class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                            Perusahaan Baru Dibuat
                        </div>
                        <div class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                            {{ number_format($this->saveResult['perusahaan_dibuat']) }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Entitas perusahaan baru terdaftar
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white/80 p-4 backdrop-blur-xs dark:border-slate-800 dark:bg-slate-800/80">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                            Baris Dilewati
                        </div>
                        <div class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                            {{ number_format($this->saveResult['dilewati']) }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Duplikat nomor/nama atau data tak lengkap
                        </p>
                    </div>
                </div>

                {{-- Action CTAs --}}
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-filament::button
                        color="primary"
                        tag="a"
                        href="{{ route('filament.admin.resources.kontaks.index') }}"
                        icon="heroicon-m-arrow-top-right-on-square"
                    >
                        Buka Daftar Kontak
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        tag="a"
                        href="{{ route('filament.admin.resources.perusahaans.index') }}"
                        icon="heroicon-m-building-office-2"
                    >
                        Buka Perusahaan
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        wire:click="resetImport"
                        icon="heroicon-m-arrow-path"
                    >
                        Import File Lain
                    </x-filament::button>
                </div>
            </div>
        </div>

    @elseif ($this->previews)
        {{-- ============================================================ --}}
        {{-- STEP 2: PRATINJAU & VERIFIKASI HASIL ANALISIS                 --}}
        {{-- ============================================================ --}}
        <div class="space-y-4">
            {{-- Action Header Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white" style="font-family: var(--font-display);">
                        Hasil Analisis Import &bull; Siap Simpan
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Periksa kontak baru, pencocokan entitas perusahaan, dan deteksi duplikat sebelum dimasukkan ke database.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-filament::button
                        color="success"
                        icon="heroicon-m-check-badge"
                        wire:click="saveImport"
                        wire:loading.attr="disabled"
                    >
                        Simpan {{ $this->counts['baru'] + $this->counts['cocok'] }} kontak baru
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-table-cells"
                        wire:click="backToPreview"
                    >
                        Kembali ke pratinjau
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-path"
                        wire:click="resetImport"
                    >
                        Ganti file
                    </x-filament::button>
                </div>
            </div>

            {{-- Metric Summary Cards (Interactive Click-to-Filter) --}}
            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                {{-- Kontak Baru --}}
                <div
                    wire:click="setQuickFilter('dibuat', '')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border p-3.5 transition-all duration-150 {{ $this->statusFilterPreviews === 'dibuat' && $this->companyFilterPreviews === '' ? 'border-success-500 bg-success-50/70 ring-2 ring-success-500/20 dark:bg-success-950/20' : 'border-slate-200/80 bg-white hover:border-success-300 dark:border-slate-800 dark:bg-slate-900' }}"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-success-600 dark:text-success-400">
                        <span>Kontak Baru</span>
                        <x-filament::badge color="success" size="sm">Siap</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['kontak_baru'] ?? ($this->counts['baru'] + $this->counts['cocok']) }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Akan dibuat &amp; disimpan
                    </div>
                </div>

                {{-- Perusahaan Baru --}}
                <div
                    wire:click="setQuickFilter('', 'baru')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border p-3.5 transition-all duration-150 {{ $this->companyFilterPreviews === 'baru' ? 'border-primary-500 bg-primary-50/70 ring-2 ring-primary-500/20 dark:bg-primary-950/20' : 'border-slate-200/80 bg-white hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900' }}"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                        <span>Perusahaan Baru</span>
                        <x-filament::badge color="primary" size="sm">Baru</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['perusahaan_baru'] ?? $this->counts['baru'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Perusahaan baru dibuat
                    </div>
                </div>

                {{-- Dihubungkan --}}
                <div
                    wire:click="setQuickFilter('', 'cocok')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border p-3.5 transition-all duration-150 {{ $this->companyFilterPreviews === 'cocok' ? 'border-indigo-500 bg-indigo-50/70 ring-2 ring-indigo-500/20 dark:bg-indigo-950/20' : 'border-slate-200/80 bg-white hover:border-indigo-300 dark:border-slate-800 dark:bg-slate-900' }}"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        <span>Dihubungkan</span>
                        <x-filament::badge color="info" size="sm">Cocok</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['cocok'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Ke perusahaan yang sudah ada
                    </div>
                </div>

                {{-- Duplikat --}}
                <div
                    wire:click="setQuickFilter('duplikat', '')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border p-3.5 transition-all duration-150 {{ in_array($this->statusFilterPreviews, ['duplikat', 'duplikat_telepon', 'duplikat_nama', 'duplikat_batch'], true) ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/20 dark:bg-amber-950/20' : 'border-slate-200/80 bg-white hover:border-amber-300 dark:border-slate-800 dark:bg-slate-900' }}"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                        <span>Duplikat</span>
                        <x-filament::badge color="warning" size="sm">Lewat</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['duplikat'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Nomor / nama sudah ada
                    </div>
                </div>

                {{-- Data Tidak Lengkap --}}
                <div
                    wire:click="setQuickFilter('data_tidak_lengkap', '')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border p-3.5 transition-all duration-150 {{ $this->statusFilterPreviews === 'data_tidak_lengkap' ? 'border-rose-500 bg-rose-50/70 ring-2 ring-rose-500/20 dark:bg-rose-950/20' : 'border-slate-200/80 bg-white hover:border-rose-300 dark:border-slate-800 dark:bg-slate-900' }}"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        <span>Tidak Lengkap</span>
                        <x-filament::badge color="danger" size="sm">Skip</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['data_tidak_lengkap'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Baris dilewati otomatis
                    </div>
                </div>

                {{-- Format Khusus / Luar Negeri --}}
                <div
                    wire:click="setQuickFilter('', '')"
                    class="import-stat-card group relative cursor-pointer rounded-xl border border-slate-200/80 bg-white p-3.5 transition-all duration-150 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <span>Format Khusus</span>
                        <x-filament::badge color="gray" size="sm">Flag</x-filament::badge>
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-mono">
                        {{ $this->counts['nomor_tidak_valid'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        Disimpan dgn flag non-standar
                    </div>
                </div>
            </div>

            {{-- Inline Editor Panel --}}
            @if ($this->editingIndex !== null)
                <div class="rounded-xl border border-primary-500/40 bg-primary-50/40 p-4 shadow-sm dark:border-primary-500/30 dark:bg-primary-950/20" wire:key="edit-panel-{{ $this->editingIndex }}">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-primary-200/60 pb-3 dark:border-primary-800/40">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-600 text-xs font-bold text-white">
                                #{{ $this->editingIndex + 1 }}
                            </span>
                            <span class="text-sm font-semibold text-primary-900 dark:text-primary-100">
                                Edit Baris Hasil Analisis
                            </span>
                        </div>
                        <p class="text-xs text-primary-700 dark:text-primary-300">
                            Setelah disimpan, baris dianalisis ulang otomatis (kelengkapan, duplikat, dan sanitasi nomor).
                        </p>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Perusahaan</label>
                            <input
                                type="text"
                                wire:model.blur="editNamaPerusahaan"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Industri</label>
                            <input
                                type="text"
                                wire:model.blur="editIndustri"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Kontak</label>
                            <input
                                type="text"
                                wire:model.blur="editNama"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">No. Telepon (Mentah)</label>
                            <input
                                type="text"
                                wire:model.blur="editNoTelepon"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-mono text-slate-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Catatan</label>
                            <input
                                type="text"
                                wire:model.blur="editCatatan"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-filament::button size="sm" color="primary" wire:click="saveEdit" icon="heroicon-m-check">
                            Simpan Perubahan
                        </x-filament::button>
                        <x-filament::button size="sm" color="gray" wire:click="cancelEdit">
                            Batal
                        </x-filament::button>
                    </div>
                </div>
            @endif

            {{-- Search & Filter Toolbar with Client-Side Fast Controls --}}
            <div
                x-data="{
                    page: 1,
                    perPage: 25,
                    get totalRows() { return {{ count($this->filteredPreviews()) }}; },
                    get totalPages() {
                        if (this.perPage === 'all') return 1;
                        return Math.max(1, Math.ceil(this.totalRows / parseInt(this.perPage)));
                    },
                    prevPage() { if (this.page > 1) this.page--; },
                    nextPage() { if (this.page < this.totalPages) this.page++; },
                    isRowVisible(idx) {
                        if (this.perPage === 'all') return true;
                        const pp = parseInt(this.perPage);
                        return idx >= (this.page - 1) * pp && idx < this.page * pp;
                    }
                }"
                x-effect="if (page > totalPages) page = totalPages || 1;"
                class="space-y-3"
            >
                <div class="flex flex-wrap items-center gap-2.5 rounded-xl border border-slate-200/80 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    {{-- Search Input --}}
                    <div class="relative min-w-[220px] flex-1">
                        <x-filament::icon alias="heroicon-o-magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="searchPreviews"
                            placeholder="Cari perusahaan, kontak, nomor telepon, catatan..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-1.5 pl-9 pr-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                    </div>

                    {{-- Sheet Selector --}}
                    @if (count($this->previewSheetOptions()) > 1)
                        <select
                            wire:model.live="sheetFilterPreviews"
                            class="rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">Semua sheet</option>
                            @foreach ($this->previewSheetOptions() as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    @endif

                    {{-- Company Filter --}}
                    <select
                        wire:model.live="companyFilterPreviews"
                        class="rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option value="">Semua status perusahaan</option>
                        <option value="baru">Perusahaan baru</option>
                        <option value="cocok">Perusahaan sudah ada</option>
                    </select>

                    {{-- Status / Keputusan Filter --}}
                    <select
                        wire:model.live="statusFilterPreviews"
                        class="rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option value="">Semua keputusan</option>
                        @foreach ($this->statusFilterOptions() as $status => $label)
                            <option value="{{ $status }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    {{-- Reset Filter Button --}}
                    @if ($this->searchPreviews !== '' || $this->sheetFilterPreviews !== '' || $this->statusFilterPreviews !== '' || $this->companyFilterPreviews !== '')
                        <x-filament::button
                            color="gray"
                            size="xs"
                            wire:click="resetFilters"
                            class="fi-btn-reset-filter"
                            icon="heroicon-m-arrow-path"
                        >
                            Reset filter
                        </x-filament::button>
                    @endif

                    {{-- Row Count & Loading --}}
                    <div class="ml-auto flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>
                            Menampilkan <strong class="text-slate-900 dark:text-white font-mono">{{ count($this->filteredPreviews()) }}</strong> dari {{ count($this->previews) }} baris
                        </span>
                        <x-filament::loading-indicator
                            wire:loading
                            wire:target="searchPreviews,sheetFilterPreviews,companyFilterPreviews,statusFilterPreviews"
                            class="h-3.5 w-3.5 text-primary-500"
                            style="display:none"
                        />
                    </div>
                </div>

                {{-- Table Container --}}
                <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400">
                                <tr>
                                    <th class="px-3.5 py-2.5 w-12 text-center">#</th>
                                    @if (count($this->previewSheetOptions()) > 1)
                                        <th class="px-3.5 py-2.5">Sheet</th>
                                    @endif
                                    <th class="px-3.5 py-2.5">Perusahaan</th>
                                    <th class="px-3.5 py-2.5">Kontak</th>
                                    <th class="px-3.5 py-2.5">No. Telepon</th>
                                    <th class="px-3.5 py-2.5">Keputusan</th>
                                    <th class="px-3.5 py-2.5 text-right w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @forelse ($this->filteredPreviews() as $index => $p)
                                    @php
                                        $perusahaanTone = $p['perusahaan_status'] === 'cocok' ? 'info' : 'success';
                                        $kontakTone = match ($p['status_kontak']) {
                                            'dibuat' => 'success',
                                            'duplikat_telepon', 'duplikat_nama', 'duplikat_batch' => 'warning',
                                            'data_tidak_lengkap' => 'danger',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <tr
                                        x-show="isRowVisible({{ $loop->index }}) || {{ $this->editingIndex === $index ? 'true' : 'false' }}"
                                        x-cloak
                                        class="transition-colors duration-100 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 {{ $this->editingIndex === $index ? 'bg-primary-50/60 ring-1 ring-inset ring-primary-500/30 dark:bg-primary-950/20' : '' }}"
                                        wire:key="preview-row-{{ $index }}"
                                    >
                                        {{-- Row Number --}}
                                        <td class="px-3.5 py-2 text-center font-mono text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $p['baris'] }}
                                        </td>

                                        {{-- Sheet Name (if multi-sheet) --}}
                                        @if (count($this->previewSheetOptions()) > 1)
                                            <td class="px-3.5 py-2 text-slate-500 dark:text-slate-400">
                                                {{ $p['sheet'] ?? '-' }}
                                            </td>
                                        @endif

                                        {{-- Perusahaan --}}
                                        <td class="px-3.5 py-2">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-medium text-slate-900 dark:text-white">
                                                    {{ $p['nama_perusahaan'] ?: '-' }}
                                                </span>
                                                <x-filament::badge :color="$perusahaanTone" size="sm">
                                                    {{ $p['perusahaan_status'] === 'cocok' ? 'sudah ada' : 'baru' }}
                                                </x-filament::badge>
                                            </div>
                                            @if ($p['industri'])
                                                <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                                    {{ $p['industri'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Kontak --}}
                                        <td class="px-3.5 py-2">
                                            @if ($p['nama'] !== '')
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $p['nama'] }}</span>
                                            @else
                                                <span class="italic text-slate-400 dark:text-slate-500">(Tanpa PIC)</span>
                                            @endif
                                        </td>

                                        {{-- No. Telepon --}}
                                        <td class="px-3.5 py-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono text-xs font-medium text-slate-800 dark:text-slate-200">
                                                    {{ $p['no_telepon'] ?: '-' }}
                                                </span>
                                                @if ($p['no_telepon_valid'])
                                                    <x-filament::badge color="success" size="sm">valid</x-filament::badge>
                                                @elseif ($p['no_telepon'] !== '')
                                                    <x-filament::badge color="warning" size="sm">tak valid</x-filament::badge>
                                                @endif
                                            </div>
                                            @if ($p['no_telepon_mentah'] && $p['no_telepon_mentah'] !== $p['no_telepon'])
                                                <div class="text-[10px] font-mono text-slate-400">
                                                    Asli: {{ $p['no_telepon_mentah'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Keputusan --}}
                                        <td class="px-3.5 py-2">
                                            <x-filament::badge :color="$kontakTone" size="sm">
                                                {{ $this->kontakStatusLabel($p['status_kontak'], $p['perusahaan_status']) }}
                                            </x-filament::badge>
                                            @if ($p['alasan'])
                                                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                                    {{ $p['alasan'] }}
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-3.5 py-2 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <x-filament::icon-button
                                                    icon="heroicon-o-pencil-square"
                                                    tooltip="Edit baris"
                                                    size="sm"
                                                    color="gray"
                                                    wire:click="startEdit({{ $index }})"
                                                />
                                                <x-filament::icon-button
                                                    icon="heroicon-o-trash"
                                                    tooltip="Hapus baris"
                                                    size="sm"
                                                    color="danger"
                                                    wire:click="deletePreview({{ $index }})"
                                                    wire:confirm="Hapus baris ini dari analisis? Baris tidak akan disimpan."
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($this->previewSheetOptions()) > 1 ? 7 : 6 }}" class="px-4 py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                <x-filament::icon alias="heroicon-o-funnel" class="h-5 w-5" />
                                            </div>
                                            <p class="mt-2 font-medium">Tidak ada baris yang cocok dengan kriteria filter.</p>
                                            <p class="text-[11px] text-slate-400">Coba atur ulang pencarian atau pilihan filter di atas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Client-Side Pagination Controls --}}
                    @if (count($this->filteredPreviews()) > 25)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/50 px-4 py-2.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400">
                            <div class="flex items-center gap-2">
                                <span>Tampilkan:</span>
                                <select
                                    x-model="perPage"
                                    x-on:change="page = 1"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="25">25 baris</option>
                                    <option value="50">50 baris</option>
                                    <option value="100">100 baris</option>
                                    <option value="all">Semua baris</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <span x-show="perPage !== 'all'">
                                    Halaman <strong class="font-mono text-slate-900 dark:text-white" x-text="page"></strong> dari <span class="font-mono" x-text="totalPages"></span>
                                </span>

                                <div class="flex items-center gap-1" x-show="perPage !== 'all' && totalPages > 1">
                                    <button
                                        type="button"
                                        x-on:click="prevPage()"
                                        :disabled="page <= 1"
                                        class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    >
                                        Sebelumnya
                                    </button>
                                    <button
                                        type="button"
                                        x-on:click="nextPage()"
                                        :disabled="page >= totalPages"
                                        class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    >
                                        Selanjutnya
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @elseif ($this->rows)
        {{-- ============================================================ --}}
        {{-- STEP 2 (RAW ROWS): PRATINJAU BARIS MENTAH                    --}}
        {{-- ============================================================ --}}
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white" style="font-family: var(--font-display);">
                        Pratinjau Baris Mentah File
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Hasil ekstraksi seluruh sheet sebelum klasifikasi entitas. Lanjutkan ke analisis untuk memverifikasi duplikat dan menyimpan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-filament::button
                        color="primary"
                        wire:click="analyze"
                    >
                        Kembali ke hasil analisis
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-path"
                        wire:click="resetImport"
                    >
                        Ganti file
                    </x-filament::button>
                </div>
            </div>

            {{-- Search & Sheet Toolbar --}}
            <div
                x-data="{
                    page: 1,
                    perPage: 25,
                    get totalRows() { return {{ count($this->filteredRows()) }}; },
                    get totalPages() {
                        if (this.perPage === 'all') return 1;
                        return Math.max(1, Math.ceil(this.totalRows / parseInt(this.perPage)));
                    },
                    prevPage() { if (this.page > 1) this.page--; },
                    nextPage() { if (this.page < this.totalPages) this.page++; },
                    isRowVisible(idx) {
                        if (this.perPage === 'all') return true;
                        const pp = parseInt(this.perPage);
                        return idx >= (this.page - 1) * pp && idx < this.page * pp;
                    }
                }"
                x-effect="if (page > totalPages) page = totalPages || 1;"
                class="space-y-3"
            >
                <div class="flex flex-wrap items-center gap-2.5 rounded-xl border border-slate-200/80 bg-white p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative min-w-[220px] flex-1">
                        <x-filament::icon alias="heroicon-o-magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="searchRows"
                            placeholder="Cari perusahaan, nama, nomor telepon mentah, catatan..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-1.5 pl-9 pr-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                    </div>

                    @if (count($this->rowSheetOptions()) > 1)
                        <select
                            wire:model.live="sheetFilterRows"
                            class="rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">Semua sheet</option>
                            @foreach ($this->rowSheetOptions() as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if ($this->searchRows !== '' || $this->sheetFilterRows !== '')
                        <x-filament::button color="gray" size="xs" wire:click="resetFilters" class="fi-btn-reset-filter" icon="heroicon-m-arrow-path">
                            Reset filter
                        </x-filament::button>
                    @endif

                    <div class="ml-auto flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>
                            Menampilkan <strong class="text-slate-900 dark:text-white font-mono">{{ count($this->filteredRows()) }}</strong> dari {{ count($this->rows) }} baris
                        </span>
                        <x-filament::loading-indicator
                            wire:loading
                            wire:target="searchRows,sheetFilterRows"
                            class="h-3.5 w-3.5 text-primary-500"
                            style="display:none"
                        />
                    </div>
                </div>

                {{-- Raw Table --}}
                <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400">
                                <tr>
                                    <th class="px-3.5 py-2.5 w-12 text-center">#</th>
                                    @if (count($this->rowSheetOptions()) > 1)
                                        <th class="px-3.5 py-2.5">Sheet</th>
                                    @endif
                                    <th class="px-3.5 py-2.5">Perusahaan</th>
                                    <th class="px-3.5 py-2.5">Nama</th>
                                    <th class="px-3.5 py-2.5">No. Telepon (Mentah)</th>
                                    <th class="px-3.5 py-2.5">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @forelse ($this->filteredRows() as $index => $r)
                                    <tr
                                        x-show="isRowVisible({{ $loop->index }})"
                                        x-cloak
                                        class="transition-colors duration-100 hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                                    >
                                        <td class="px-3.5 py-2 text-center font-mono text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $index + 1 }}
                                        </td>
                                        @if (count($this->rowSheetOptions()) > 1)
                                            <td class="px-3.5 py-2 text-slate-500 dark:text-slate-400">
                                                {{ $r['sheet'] ?? '-' }}
                                            </td>
                                        @endif
                                        <td class="px-3.5 py-2 font-medium text-slate-900 dark:text-white">
                                            {{ $r['nama_perusahaan'] !== '' ? $r['nama_perusahaan'] : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2 text-slate-800 dark:text-slate-200">
                                            {{ $r['nama'] !== '' ? $r['nama'] : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2 font-mono text-xs text-slate-700 dark:text-slate-300">
                                            {{ $r['no_telepon_mentah'] !== '' ? $r['no_telepon_mentah'] : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2 text-slate-500 dark:text-slate-400">
                                            {{ $r['catatan'] !== '' ? $r['catatan'] : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($this->rowSheetOptions()) > 1 ? 6 : 5 }}" class="px-4 py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                            Tidak ada baris yang cocok dengan kriteria filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (count($this->filteredRows()) > 25)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/50 px-4 py-2.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400">
                            <div class="flex items-center gap-2">
                                <span>Tampilkan:</span>
                                <select
                                    x-model="perPage"
                                    x-on:change="page = 1"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="25">25 baris</option>
                                    <option value="50">50 baris</option>
                                    <option value="100">100 baris</option>
                                    <option value="all">Semua baris</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <span x-show="perPage !== 'all'">
                                    Halaman <strong class="font-mono text-slate-900 dark:text-white" x-text="page"></strong> dari <span class="font-mono" x-text="totalPages"></span>
                                </span>

                                <div class="flex items-center gap-1" x-show="perPage !== 'all' && totalPages > 1">
                                    <button
                                        type="button"
                                        x-on:click="prevPage()"
                                        :disabled="page <= 1"
                                        class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    >
                                        Sebelumnya
                                    </button>
                                    <button
                                        type="button"
                                        x-on:click="nextPage()"
                                        :disabled="page >= totalPages"
                                        class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    >
                                        Selanjutnya
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @else
        {{-- ============================================================ --}}
        {{-- STEP 1: UNGGAH FILE & PANDUAN FORMAT                          --}}
        {{-- ============================================================ --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Dropzone Area (2 cols) --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white" style="font-family: var(--font-display);">
                            Pilih atau Tarik File Spreadsheet
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Mendukung file Excel multi-sheet (.xlsx, .xls) dan teks terpisah koma/tab (.csv, .tsv, .ods) hingga 10 MB.
                        </p>
                    </div>

                    {{-- Modern Dropzone with Alpine.js --}}
                    <div
                        x-data="{
                            isDropping: false,
                            isUploading: false,
                            progress: 0,
                            fileName: '',
                            fileSize: '',
                            handleFiles(files) {
                                if (files && files.length > 0) {
                                    this.fileName = files[0].name;
                                    const bytes = files[0].size;
                                    this.fileSize = bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : (bytes / 1024).toFixed(0) + ' KB';
                                }
                            }
                        }"
                        x-on:dragover.prevent="isDropping = true"
                        x-on:dragleave.prevent="isDropping = false"
                        x-on:drop.prevent="isDropping = false"
                        x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="import-dropzone relative mt-4 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-8 text-center"
                        :class="isDropping ? 'is-dropping border-primary-500 bg-primary-50/50 dark:bg-primary-950/20' : 'border-slate-300 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-800/40'"
                    >
                        {{-- Hidden File Input covering the dropzone --}}
                        <input
                            type="file"
                            wire:model="file"
                            accept=".xlsx,.xls,.ods,.csv,.tsv"
                            x-on:change="handleFiles($event.target.files)"
                            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            title="Pilih file Excel atau CSV"
                        >

                        {{-- Icon State --}}
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-xs ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                            <x-filament::icon alias="heroicon-o-arrow-up-tray" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>

                        {{-- Main Text --}}
                        <div class="mt-3">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <span class="text-primary-600 hover:underline dark:text-primary-400">Klik untuk memilih file</span>
                                atau seret file ke sini
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Format: .xlsx, .xls, .csv, .tsv, .ods (Maksimal 10 MB)
                            </p>
                        </div>

                        {{-- Format Badges --}}
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">.XLSX</span>
                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">.XLS</span>
                            <span class="rounded-md bg-sky-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-950/40 dark:text-sky-300">.CSV</span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-600/20 dark:bg-slate-800 dark:text-slate-300">.TSV</span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-600/20 dark:bg-slate-800 dark:text-slate-300">.ODS</span>
                        </div>

                        {{-- Upload Progress Animation --}}
                        <div x-show="isUploading" x-cloak class="mt-4 w-full max-w-xs space-y-1.5">
                            <div class="flex items-center justify-between text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                <span>Mengunggah file...</span>
                                <span x-text="progress + '%'" class="font-mono"></span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full bg-primary-600 transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>

                        {{-- Selected File Card --}}
                        <div x-show="fileName !== '' && !isUploading" x-cloak class="mt-4 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 shadow-2xs dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <x-filament::icon alias="heroicon-m-document-text" class="h-4 w-4 text-primary-500" />
                            <span x-text="fileName" class="font-medium"></span>
                            <span x-text="'(' + fileSize + ')'" class="text-slate-400 font-mono text-[11px]"></span>
                            <span class="inline-flex items-center text-success-600 dark:text-success-400">
                                <x-filament::icon alias="heroicon-m-check-circle" class="h-4 w-4" />
                            </span>
                        </div>
                    </div>

                    {{-- Error Message --}}
                    @error('file')
                        <p class="mt-2 text-xs font-medium text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- Action Buttons --}}
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <x-filament::button
                            wire:click="preview"
                            wire:loading.attr="disabled"
                            wire:target="preview,file"
                        >
                            Pratinjau &amp; Analisis
                        </x-filament::button>

                        <x-filament::button
                            color="gray"
                            wire:click="downloadTemplate"
                            icon="heroicon-m-arrow-down-tray"
                        >
                            Unduh template (CSV)
                        </x-filament::button>
                    </div>
                </div>
            </div>

            {{-- Guide / Side Info (1 col) --}}
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <h4 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white" style="font-family: var(--font-display);">
                        Deteksi Header Otomatis
                    </h4>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Sistem mencocokkan header secara cerdas (Indonesia / Inggris, kapital / huruf kecil, atau kolom gabungan).
                    </p>

                    <div class="mt-3 space-y-2 text-xs">
                        <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Kolom Wajib Ada:</span>
                            <ul class="mt-1 list-inside list-disc space-y-0.5 text-[11px] text-slate-600 dark:text-slate-400">
                                <li><code>nama_perusahaan</code> (atau <em>perusahaan</em>)</li>
                                <li><code>nama</code> (atau <em>pic</em>)</li>
                                <li><code>no_telepon</code> (atau <em>hp</em> / <em>telp</em>)</li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Kolom Opsional:</span>
                            <p class="mt-0.5 text-[11px] text-slate-600 dark:text-slate-400">
                                <code>industri</code>, <code>jabatan</code>, <code>catatan</code>.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400 space-y-2">
                        <div class="flex items-start gap-1.5">
                            <x-filament::icon alias="heroicon-m-sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary-500" />
                            <span><strong>Pemisahan Cerdas:</strong> Jika satu sel memuat banyak PIC (misal: "Budi / Siti"), sistem memecahnya per baris kontak secara rapi.</span>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <x-filament::icon alias="heroicon-m-phone" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-success-500" />
                            <span><strong>Sanitasi Nomor:</strong> Nomor HP distandardisasi otomatis ke 628..., sementara nomor telepon kantor/PSTN tetap dilindungi formatnya.</span>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <x-filament::icon alias="heroicon-m-shield-check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-indigo-500" />
                            <span><strong>Pencegahan Duplikat:</strong> Nomor telepon dan nama kontak yang sudah ada di database tidak akan dibuat berulang kali.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
