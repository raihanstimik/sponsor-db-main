@php
    /** @var array<int, string> $warnaBaris */
    $warnaTervalidasi = collect($warnaBaris ?? [])
        ->filter(fn (string $hex): bool => preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1)
        ->values();
@endphp

@if ($warnaTervalidasi->isNotEmpty())
    {{-- Pewarnaan baris menurut kegiatan/kategori; tint tipis agar teks tetap terbaca. --}}
    <style>
        @foreach ($warnaTervalidasi as $hex)
            tr.{{ \App\Filament\Resources\Kontaks\Tables\KontaksTable::kelasWarnaBaris($hex) }} > td {
                background-color: {{ strtolower($hex) }}1f !important;
            }
        @endforeach
    </style>
@endif

<div class="fi-ta-kontak-summary grid gap-2 sm:gap-3 grid-cols-2 lg:grid-cols-4">
    @foreach ($cards as $card)
        <div
            @if ($card['key'] === 'total')
                wire:click="resetFilters"
                role="button"
                tabindex="0"
                title="Klik untuk mereset seluruh filter"
            @endif
            class="flex items-center gap-2.5 sm:gap-3 rounded-xl border p-2.5 sm:p-3 transition-all duration-150 select-none {{ $card['key'] === 'total' ? 'cursor-pointer' : '' }} border-gray-200/80 bg-white dark:border-gray-700/60 dark:bg-gray-900 hover:border-gray-300 hover:shadow-sm hover:-translate-y-0.5 dark:hover:bg-gray-800/80"
        >
            <span
                class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg transition-transform"
                style="background-color: {{ $card['color'] }}1a"
            >
                <x-filament::icon :icon="$card['icon']" class="h-4 w-4 sm:h-5 sm:w-5" style="color: {{ $card['color'] }}" />
            </span>

            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <div class="text-lg sm:text-xl font-semibold leading-none tabular-nums text-gray-950 dark:text-white">
                        {{ number_format($card['count'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ $card['label'] }}
                </div>
            </div>
        </div>
    @endforeach
</div>