<x-filament-panels::page>
    <div
        x-data="{
            tab: @js($tab),
            switchTab(newTab) {
                this.tab = newTab;
                $wire.setTab(newTab);
            }
        }"
        class="flex flex-col gap-4"
    >
        {{-- Executive Segmented Tab Bar (Instant Alpine Switching) --}}
        <div class="flex items-center justify-between">
            <div class="inline-flex p-1 bg-gray-100 dark:bg-white/5 rounded-xl border border-gray-200/80 dark:border-white/10 shadow-xs">
                <button
                    type="button"
                    @click="switchTab('kegiatan')"
                    :class="tab === 'kegiatan'
                        ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold'
                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer select-none"
                >
                    <x-filament::icon
                        icon="heroicon-o-calendar-days"
                        class="w-4 h-4 transition-colors"
                        ::class="tab === 'kegiatan' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                    />
                    <span>Daftar Kegiatan / Event</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-[11px] font-bold transition-colors"
                        :class="tab === 'kegiatan'
                            ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300'
                            : 'bg-gray-200/70 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                    >
                        {{ $this->getTotalKegiatan() }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="switchTab('kategori')"
                    :class="tab === 'kategori'
                        ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-800 dark:text-white font-semibold'
                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer select-none"
                >
                    <x-filament::icon
                        icon="heroicon-o-folder"
                        class="w-4 h-4 transition-colors"
                        ::class="tab === 'kategori' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                    />
                    <span>Kelola Kategori Spesialisasi</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-[11px] font-bold transition-colors"
                        :class="tab === 'kategori'
                            ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300'
                            : 'bg-gray-200/70 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                    >
                        {{ $this->getTotalKategori() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Instant Tab Panels (Both pre-rendered, toggled with 0ms delay) --}}
        <div x-show="tab === 'kegiatan'" x-cloak>
            {{ $this->content }}
        </div>

        <div x-show="tab === 'kategori'" x-cloak>
            @livewire(\App\Filament\Resources\Kegiatans\Widgets\KategoriKegiatanTableWidget::class)
        </div>
    </div>
</x-filament-panels::page>
