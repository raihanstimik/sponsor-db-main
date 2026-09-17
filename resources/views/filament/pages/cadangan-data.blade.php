<x-filament-panels::page>
    {{-- Row 1: Status Ringkasan Cadangan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Total File & Kapasitas --}}
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Penyimpanan Cadangan</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $storage['formatted'] }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Terpakai</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Total Berkas:</span>
                <strong class="text-slate-800 dark:text-slate-200">{{ $storage['count'] }} Berkas ZIP</strong>
            </div>
        </div>

        {{-- Cadangan Terakhir --}}
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Cadangan Terakhir</span>
                <div class="mt-2 flex items-baseline gap-2">
                    @if (count($backups) > 0)
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $backups[0]['created_at']->diffForHumans() }}</span>
                    @else
                        <span class="text-sm font-medium text-slate-400">Belum ada cadangan</span>
                    @endif
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Waktu Lengkap:</span>
                <span class="font-medium text-slate-700 dark:text-slate-300">
                    {{ count($backups) > 0 ? $backups[0]['created_at']->format('d M Y, H:i') : '-' }}
                </span>
            </div>
        </div>

        {{-- Mesin Basis Data --}}
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Koneksi Basis Data</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-xl font-bold uppercase text-slate-900 dark:text-white">{{ $dbDriver }}</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Siap
                    </span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Integritas Skema:</span>
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $totalTables }} Tabel Terproteksi</span>
            </div>
        </div>
    </div>

    {{-- Row 2: Dua Tindakan Utama (Buat Snapshot & Unggah Cadangan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Card Kiri: Buat Cadangan Baru --}}
        <div class="lg:col-span-7 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Buat Snapshot Cadangan Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ekstrak seluruh basis data ke dalam arsip terkompresi ZIP portabel dengan checksum integritas SHA-256.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Cadangan (Opsional):</label>
                    <input 
                        type="text" 
                        wire:model="catatanBaru" 
                        placeholder="Contoh: Sebelum migrasi massal atau rilis kegiatan baru"
                        class="w-full h-10 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 focus:ring-2 focus:ring-blue-600/30 focus:border-blue-600"
                    />
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button 
                    wire:click="buatCadangan" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="h-9 inline-flex items-center justify-center gap-2 px-4 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition-all shadow-xs disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="buatCadangan" class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Cadangan Sekarang
                    </span>
                    <span wire:loading wire:target="buatCadangan" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses Snapshot...
                    </span>
                </button>
            </div>
        </div>

        {{-- Card Kanan: Unggah Berkas Cadangan Eksternal --}}
        <div class="lg:col-span-5 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Unggah Berkas Cadangan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Impor file arsip ZIP cadangan ICM dari komputer lokal.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Berkas ZIP (Maks. 50 MB):</label>
                    <input 
                        type="file" 
                        wire:model="berkasUpload" 
                        accept=".zip"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-950 dark:file:text-violet-300 cursor-pointer"
                    />
                    @error('berkasUpload') 
                        <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button 
                    wire:click="unggahCadangan" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="h-9 inline-flex items-center justify-center gap-2 px-4 rounded-xl text-xs font-semibold text-white bg-violet-600 hover:bg-violet-700 active:scale-98 transition-all shadow-xs disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="unggahCadangan" class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        Verifikasi &amp; Simpan
                    </span>
                    <span wire:loading wire:target="unggahCadangan" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Mengunggah...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Row 3: Tabel Riwayat File Cadangan --}}
    <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Berkas Cadangan</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar snapshot basis data yang tersimpan di server. Sebelum proses pemulihan, sistem otomatis mencadangkan data terkini (*Pre-Restore Safety Snapshot*).</p>
            </div>

            @if (count($backups) > 0)
                <button 
                    wire:click="bersihkanCadanganLama" 
                    onclick="return confirm('Hapus semua berkas cadangan reguler yang berusia lebih dari 30 hari?');"
                    type="button" 
                    class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                >
                    Bersihkan Arsip &gt; 30 Hari
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-semibold uppercase text-[10px]">
                        <th class="pb-2.5 pr-3">Nama Berkas</th>
                        <th class="pb-2.5 px-3">Waktu Pembuatan</th>
                        <th class="pb-2.5 px-3">Dibuat Oleh</th>
                        <th class="pb-2.5 px-3 text-center">Ukuran</th>
                        <th class="pb-2.5 px-3">Cakupan Data</th>
                        <th class="pb-2.5 pl-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse ($backups as $b)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ $b['is_pre_restore'] ? 'bg-amber-50/30 dark:bg-amber-950/10' : '' }}">
                            <td class="py-3 pr-3">
                                <div class="flex items-center gap-2">
                                    @if ($b['is_pre_restore'])
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $b['filename'] }}</span>
                                            @if ($b['is_pre_restore'])
                                                <span class="inline-flex items-center text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 px-1.5 py-0.5 rounded">
                                                    Safety Pre-Restore
                                                </span>
                                            @endif
                                        </div>
                                        @if ($b['catatan'])
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $b['catatan'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $b['created_at']->format('d M Y, H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b['created_at']->diffForHumans() }}</div>
                            </td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-400">
                                {{ $b['created_by'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-semibold text-slate-800 dark:text-slate-200">
                                {{ $b['size_formatted'] }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $b['table_summary'] }}
                                </span>
                            </td>
                            <td class="py-3 pl-3 text-right">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    {{-- Unduh --}}
                                    <button 
                                        wire:click="unduhCadangan('{{ $b['filename'] }}')" 
                                        type="button" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/50 dark:hover:bg-blue-900/60 dark:text-blue-400 transition-colors"
                                        title="Unduh Berkas ZIP"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Unduh
                                    </button>

                                    {{-- Pulihkan (Restore) --}}
                                    <button 
                                        wire:click="pulihkanCadangan('{{ $b['filename'] }}')" 
                                        onclick="return confirm('PERINGATAN PEMULIHAN BASIS DATA:\n\nApakah Anda yakin ingin memulihkan database dari arsip {{ $b['filename'] }}?\n\nSistem akan secara otomatis membuat snapshot pengaman darurat (Pre-Restore) dari kondisi saat ini sebelum data ditimpa.');"
                                        type="button" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 dark:text-amber-300 transition-colors"
                                        title="Pulihkan Basis Data"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Pulihkan
                                    </button>

                                    {{-- Hapus --}}
                                    <button 
                                        wire:click="hapusCadangan('{{ $b['filename'] }}')" 
                                        onclick="return confirm('Hapus permanen berkas cadangan {{ $b['filename'] }} dari server?');"
                                        type="button" 
                                        class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 dark:text-rose-400 transition-colors"
                                        title="Hapus Cadangan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                Belum ada berkas cadangan. Klik "Buat Cadangan Sekarang" untuk membuat arsip database pertama Anda, atau unggah file ZIP dari komputer Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>

