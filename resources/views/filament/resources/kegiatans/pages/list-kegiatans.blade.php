<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Stitch Tab Navigation Bar --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10">
            <div class="flex gap-4">
                <button
                    type="button"
                    wire:click="setTab('kegiatan')"
                    class="flex items-center gap-2 pb-3 text-sm font-semibold transition-colors border-b-2 {{ $tab === 'kegiatan' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5" />
                    <span>Daftar Kegiatan / Event</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $tab === 'kegiatan' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                        {{ $this->getTotalKegiatan() }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setTab('kategori')"
                    class="flex items-center gap-2 pb-3 text-sm font-semibold transition-colors border-b-2 {{ $tab === 'kategori' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    <x-filament::icon icon="heroicon-o-folder" class="w-5 h-5" />
                    <span>Kelola Kategori Spesialisasi</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $tab === 'kategori' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                        {{ $this->getTotalKategori() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Dynamic Tab Content --}}
        @if ($tab === 'kegiatan')
            {{ $this->content }}
        @else
            @livewire(\App\Filament\Resources\Kegiatans\Widgets\KategoriKegiatanTableWidget::class)
        @endif
    </div>
</x-filament-panels::page>

