<x-filament-widgets::widget>
<div
    class="fi-wi-sambutan relative w-full overflow-hidden rounded-2xl px-6 py-5 shadow-lg sm:py-6"
>
    <div class="pointer-events-none absolute -left-12 -top-12 h-48 w-48 rounded-full bg-sky-500/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 -bottom-10 h-52 w-52 rounded-full bg-[#EA7C1A]/25 blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-2">

        <div>
            <h1 class="font-display text-xl sm:text-2xl font-bold tracking-tight text-white">
                Selamat datang kembali, {{ auth()->user()?->name ?? 'Pengguna' }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-slate-200">
                Ringkasan Arsip Digital Sponsor &amp; Kontak PIC Indonesia Congress Management
            </p>
        </div>
    </div>
</div>
</x-filament-widgets::widget>
