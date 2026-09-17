@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeClass' => 'bg-slate-100 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    'icon' => null,
    'footerText' => null,
    'footerUrl' => null,
    'footerLabel' => null,
    'footerActionClass' => 'text-blue-600 hover:underline dark:text-sky-400',
])

<div {{ $attributes->merge(['class' => 'dashboard-card flex flex-col justify-between rounded-2xl bg-white p-5 sm:p-6 border border-slate-100/90 shadow-xs transition-colors duration-150 hover:border-slate-200 dark:bg-gray-900 dark:border-slate-800 dark:hover:border-slate-700']) }}>
    <div>
        <div class="mb-5 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($badge)
                <div class="shrink-0 rounded-full {{ $badgeClass }}">{!! $badge !!}</div>
            @endif
        </div>

        {{ $slot }}
    </div>

    @if ($footerText || $footerUrl || isset($footer))
        <div class="mt-6 flex items-center justify-between gap-2 border-t border-slate-100/80 pt-3 text-xs dark:border-slate-800/80">
            @if (isset($footer))
                {{ $footer }}
            @else
                <span class="text-xs text-slate-400 dark:text-slate-500">{!! $footerText !!}</span>
                @if ($footerUrl && $footerLabel)
                    <a href="{{ $footerUrl }}" wire:navigate class="inline-flex items-center gap-1 shrink-0 font-semibold {{ $footerActionClass }} transition-colors text-xs">
                        <span>{!! $footerLabel !!}</span>
                    </a>
                @endif
            @endif
        </div>
    @endif
</div>