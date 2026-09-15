@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeClass' => 'bg-slate-100 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    'icon' => null,
    'footerText' => null,
    'footerUrl' => null,
    'footerLabel' => null,
    'footerActionClass' => 'text-[#18225E] hover:underline dark:text-sky-400',
])

<div {{ $attributes->merge(['class' => 'dashboard-card flex flex-col justify-between rounded-xl bg-white p-5 border border-slate-200/90 shadow-xs transition-colors duration-150 hover:border-slate-300 dark:bg-gray-900 dark:border-slate-800 dark:hover:border-slate-700 sm:p-6']) }}>
    <div>
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($badge)
                <span class="shrink-0 rounded px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">{{ $badge }}</span>
            @endif
        </div>

        {{ $slot }}
    </div>

    @if ($footerText || $footerUrl || isset($footer))
        <div class="mt-5 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs dark:border-slate-800">
            @if (isset($footer))
                {{ $footer }}
            @else
                <span class="text-slate-500 dark:text-slate-400">{{ $footerText }}</span>
                @if ($footerUrl && $footerLabel)
                    <a href="{{ $footerUrl }}" wire:navigate class="inline-flex items-center gap-1 shrink-0 font-medium {{ $footerActionClass }} transition-colors">
                        <span>{{ $footerLabel }}</span>
                    </a>
                @endif
            @endif
        </div>
    @endif
</div>