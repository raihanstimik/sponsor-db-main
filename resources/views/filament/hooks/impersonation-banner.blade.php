@php
    $impersonateManager = app(\Lab404\Impersonate\Services\ImpersonateManager::class);
@endphp

@if ($impersonateManager->isImpersonating())
    <div class="fi-impersonation-banner relative z-30 flex flex-wrap items-center justify-between gap-2 border-b border-amber-300 bg-amber-400 px-4 py-2 text-amber-950 shadow-sm sm:px-6">
        <div class="flex items-center gap-x-2 text-xs sm:text-sm font-semibold">
            <x-filament::icon
                icon="heroicon-m-eye"
                class="h-5 w-5 text-amber-950 shrink-0"
            />
            <span>
                Mode Impersonasi: Sedang melihat sebagai <strong>{{ auth()->user()?->name }}</strong> ({{ auth()->user()?->email }}).
            </span>
        </div>
        <div>
            <a
                href="{{ route('impersonate.leave') }}"
                class="inline-flex items-center gap-x-1.5 rounded-md bg-amber-950 px-3 py-1 text-xs font-bold text-amber-50 shadow-sm transition hover:bg-black focus:outline-none focus:ring-2 focus:ring-amber-900"
            >
                <x-filament::icon
                    icon="heroicon-m-arrow-left-on-rectangle"
                    class="h-4 w-4"
                />
                <span>Kembali ke Akun Admin</span>
            </a>
        </div>
    </div>
@endif
