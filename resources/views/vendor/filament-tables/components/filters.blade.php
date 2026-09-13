@php
    use Filament\Tables\Enums\FiltersResetActionPosition;
@endphp

@props([
    'applyAction',
    'form',
    'headingTag' => 'h3',
    'resetActionPosition' => FiltersResetActionPosition::Header,
])

<div {{ $attributes->class(['fi-ta-filters']) }}>
    <div class="fi-ta-filters-header flex items-center justify-between">
        <{{ $headingTag }} class="fi-ta-filters-heading">
            {{ __('filament-tables::table.filters.heading') }}
        </{{ $headingTag }}>

        @if ($resetActionPosition === FiltersResetActionPosition::Header)
            <div>
                <x-filament::button
                    color="gray"
                    size="sm"
                    tag="button"
                    wire:click="resetTableFiltersForm"
                    icon="heroicon-m-arrow-path"
                    class="fi-btn-reset-filter transition-all duration-200 hover:shadow-xs active:scale-95"
                >
                    Reset Filter
                </x-filament::button>
            </div>
        @endif
    </div>

    {{ $form }}

    @if ($applyAction->isVisible() || $resetActionPosition === FiltersResetActionPosition::Footer)
        <div class="fi-ta-filters-actions-ctn">
            @if ($applyAction->isVisible())
                {{ $applyAction }}
            @endif

            @if ($resetActionPosition === FiltersResetActionPosition::Footer)
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="resetTableFiltersForm"
                    icon="heroicon-m-arrow-path"
                    class="fi-btn-reset-filter transition-all duration-200 active:scale-95"
                >
                    Reset Filter
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
