@php
    /** @var \App\Models\User $user */
    $user = $getRecord();
    $avatarUrl = $user->getFilamentAvatarUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=18225E&background=E0E7FF&bold=true';
    $roleName = $user->roles->first()?->name ?? $user->role ?? 'karyawan';
@endphp

<div class="space-y-3.5 py-1">
    {{-- Kartu Profil Utama (Simetris & Kompak) --}}
    <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 text-center shadow-xs dark:border-slate-800 dark:bg-slate-800/60">
        {{-- Avatar Tengah Simetris --}}
        <div class="mx-auto mb-2.5 flex justify-center">
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $user->name }}"
                class="h-16 w-16 rounded-full object-cover shadow-sm ring-2 ring-slate-200 dark:ring-slate-700"
                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=18225E&background=E0E7FF&bold=true';"
            />
        </div>

        {{-- Nama --}}
        <h3 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">
            {{ $user->name }}
        </h3>

        {{-- Badges Peran & Status Tengah --}}
        <div class="mt-1.5 flex flex-wrap items-center justify-center gap-1.5">
            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-[#18225E] ring-1 ring-inset ring-blue-700/15 dark:bg-blue-900/30 dark:text-blue-300">
                {{ ucfirst($roleName) }}
            </span>
            @if ($user->is_active)
                <span class="inline-flex items-center gap-x-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-2 w-2 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-x-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400">
                    <svg class="h-2 w-2 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                    Menunggu Approval
                </span>
            @endif
        </div>

        {{-- Email Tengah --}}
        <div class="mt-2 flex items-center justify-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
            <x-heroicon-m-envelope class="h-3.5 w-3.5 text-slate-400 shrink-0" />
            <a href="mailto:{{ $user->email }}" class="font-medium text-slate-700 hover:underline dark:text-slate-300">
                {{ $user->email }}
            </a>
        </div>
    </div>

    {{-- Grid Informasi Kepegawaian (Simetris 2 Kolom, Hemat Ruang) --}}
    <div class="grid grid-cols-2 gap-2.5">
        <div class="rounded-lg border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Divisi Kerja</div>
            <div class="mt-1">
                @if ($user->divisi?->name)
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                        {{ $user->divisi->name }}
                    </span>
                @else
                    <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">No. Telepon / WA</div>
            <div class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-800 dark:text-slate-200">
                @if (filled($user->phone))
                    <x-heroicon-m-phone class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                    <span>{{ $user->phone }}</span>
                @else
                    <span class="text-slate-400 italic font-normal">Belum tercatat</span>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Bergabung Sejak</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-slate-700 dark:text-slate-300">
                <x-heroicon-m-calendar class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                <span>{{ $user->joined_at ? $user->joined_at->format('d M Y') : '-' }}</span>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Terdaftar di Sistem</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-slate-700 dark:text-slate-300">
                <x-heroicon-m-clock class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                <span>{{ $user->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>
</div>
