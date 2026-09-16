<x-filament-widgets::widget>
<div class="fi-wi-sambutan w-full overflow-hidden rounded-xl bg-[#18225E] border-t-2 border-[#EA7C1A] px-6 py-5 shadow-xs text-white">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold tracking-wide uppercase bg-white/10 text-slate-200 border border-white/15">
                    Indonesia Congress Management
                </span>
                <span class="text-xs text-slate-300 font-medium">
                    &bull; {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                Selamat datang kembali, {{ auth()->user()?->name ?? 'Pengguna' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-slate-300 font-normal">
                Pusat kendali arsip digital sponsorship, data PIC, dan keterlibatan mitra industri medis.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a {{ \Filament\Support\generate_href_html(\App\Filament\Resources\Kontaks\KontakResource::getUrl()) }}
               x-data="{ loading: false }"
               x-on:click="if (! ($event.altKey || $event.ctrlKey || $event.metaKey || $event.shiftKey)) { loading = true }"
               x-on:livewire:navigated.window="loading = false"
               x-bind:class="{ 'opacity-75 pointer-events-none cursor-wait': loading }"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white text-[#18225E] text-xs font-semibold hover:bg-slate-100 active:bg-slate-200 transition-all shadow-xs">
                <x-filament::loading-indicator x-show="loading" x-cloak class="w-4 h-4 animate-spin text-[#18225E]" />
                <x-heroicon-m-user-group x-show="!loading" class="w-4 h-4 text-[#18225E]" />
                <span>Kelola Kontak PIC</span>
            </a>
            <a {{ \Filament\Support\generate_href_html(\App\Filament\Resources\Perusahaans\PerusahaanResource::getUrl()) }}
               x-data="{ loading: false }"
               x-on:click="if (! ($event.altKey || $event.ctrlKey || $event.metaKey || $event.shiftKey)) { loading = true }"
               x-on:livewire:navigated.window="loading = false"
               x-bind:class="{ 'opacity-75 pointer-events-none cursor-wait': loading }"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/15 active:bg-white/20 text-white text-xs font-semibold border border-white/20 transition-all">
                <x-filament::loading-indicator x-show="loading" x-cloak class="w-4 h-4 animate-spin text-white" />
                <x-heroicon-m-building-office-2 x-show="!loading" class="w-4 h-4 text-slate-200" />
                <span>Master Perusahaan</span>
            </a>
        </div>
    </div>
</div>
</x-filament-widgets::widget>