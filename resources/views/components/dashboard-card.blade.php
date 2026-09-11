@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeClass' => 'bg-slate-100 font-semibold text-slate-600 dark:bg-white/5 dark:text-gray-300',
    'dotClass' => 'bg-primary-950 dark:bg-primary-400',
    'footerText' => null,
    'footerUrl' => null,
    'footerLabel' => null,
    'footerActionClass' => 'text-primary-800 hover:underline dark:text-primary-300',
])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-950/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6']) }}>
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded {{ $dotClass }}"></span>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
            </div>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-slate-500 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($badge)
            <span class="shrink-0 rounded px-2 py-1 text-xs {{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </div>

    {{ $slot }}

    @if ($footerText || $footerUrl || isset($footer))
        <div class="mt-4 flex items-center justify-between gap-2 rounded-lg bg-slate-100/70 p-2 text-sm dark:bg-white/5">
        <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs dark:border-white/5">
            @if (isset($footer))
                {{ $footer }}
            @else
                <span class="text-slate-500 dark:text-gray-400">{{ $footerText }}</span>
                @if ($footerUrl && $footerLabel)
                    <a href="{{ $footerUrl }}" wire:navigate class="shrink-0 font-semibold {{ $footerActionClass }}">{{ $footerLabel }}</a>
                @endif
            @endif
        </div>
    @endif
</div>
