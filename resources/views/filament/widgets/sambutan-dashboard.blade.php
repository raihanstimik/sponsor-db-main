<x-filament-widgets::widget>
<div
    class="relative w-full overflow-hidden rounded-xl bg-primary-900 px-6 py-5 shadow-sm dark:bg-gray-900 dark:ring-1 dark:ring-white/10 sm:py-6"
>
    <div class="pointer-events-none absolute -right-10 -top-14 h-56 w-56 rounded-full bg-white/5 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-14 right-1/4 h-44 w-44 rounded-full bg-white/5 blur-2xl"></div>

    <div class="relative z-10 flex flex-col gap-1">
        <h1 class="font-display text-2xl font-bold tracking-tight text-white">
            Selamat datang kembali, {{ auth()->user()?->name ?? 'Pengguna' }}
        </h1>
        <p class="text-sm text-primary-100/90 dark:text-gray-400">
            Ringkasan Arsip Digital Sponsor &amp; Kontak PIC Indonesia Congress Management
        </p>
    </div>
</div>
</x-filament-widgets::widget>
</div>
