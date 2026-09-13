@props([
    'value' => 0,
    'spins' => 2,
    'duration' => 900,
    'stagger' => 50,
    'class' => '',
])

@php
    $strValue = (string) $value;
@endphp

<span
    x-data="window.spinningCounter ? window.spinningCounter({
        value: @js($strValue),
        spins: {{ (int) $spins }},
        duration: {{ (int) $duration }},
        stagger: {{ (int) $stagger }},
    }) : {}"
    data-value="{{ $strValue }}"
    aria-label="{{ $strValue }}"
    class="t-reel-container inline-flex items-center select-none font-inherit {{ $class }}"
><span
        x-ref="fallback"
        x-show="!ready"
        class="t-reel-fallback font-inherit tabular-nums leading-none"
    >{{ $strValue }}</span><span
        x-ref="reel"
        x-show="ready"
        wire:ignore
        class="t-reel"
        aria-hidden="true"
        style="display: none;"
    ></span></span>
