@php
    /** @var \App\Models\Divisi $divisi */
    $divisi = $getRecord();
    $users = $divisi->users()->with('roles')->get();
    $usersCount = $users->count();
@endphp

<div class="space-y-4 py-1">
    {{-- Kartu Utama Divisi (Simetris & Kompak) --}}
    <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 text-center shadow-xs dark:border-slate-800 dark:bg-slate-800/60">
        {{-- Ikon Divisi --}}
        <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#18225E]/10 text-[#18225E] ring-1 ring-[#18225E]/20 dark:bg-blue-900/30 dark:text-blue-300">
            <x-heroicon-o-building-office-2 class="h-7 w-7" />
        </div>

        {{-- Nama Divisi --}}
        <h3 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">
            {{ $divisi->name }}
        </h3>

        {{-- Slug & Total Anggota --}}
        <div class="mt-2 flex flex-wrap items-center justify-center gap-1.5">
            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-900 dark:text-slate-300">
                slug: {{ $divisi->slug }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-[#18225E] ring-1 ring-inset ring-blue-700/15 dark:bg-blue-900/30 dark:text-blue-300">
                <x-heroicon-m-user-group class="h-3 w-3" />
                {{ $usersCount }} Karyawan
            </span>
        </div>

        {{-- Deskripsi --}}
        @if (filled($divisi->description))
            <div class="mt-3 rounded-lg border border-slate-200/60 bg-white/80 p-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-300">
                {{ $divisi->description }}
            </div>
        @else
            <div class="mt-2 text-xs italic text-slate-400">
                Belum ada deskripsi divisi.
            </div>
        @endif
    </div>

    {{-- Daftar Anggota / Karyawan --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <span class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
                Daftar Karyawan Terdaftar ({{ $usersCount }})
            </span>
        </div>

        @if ($users->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center dark:border-slate-700">
                <x-heroicon-o-users class="mx-auto h-6 w-6 text-slate-300 dark:text-slate-600" />
                <p class="mt-1 text-xs text-slate-400">Belum ada karyawan yang ditugaskan di divisi ini.</p>
            </div>
        @else
            <div class="max-h-72 space-y-1.5 overflow-y-auto pr-0.5">
                @foreach ($users as $user)
                    @php
                        $avatarUrl = $user->getFilamentAvatarUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=18225E&background=E0E7FF&bold=true';
                        $roleName = $user->roles->first()?->name ?? $user->role ?? 'karyawan';
                    @endphp
                    <div class="flex items-center justify-between gap-2.5 rounded-lg border border-slate-200/70 bg-white p-2.5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img
                                src="{{ $avatarUrl }}"
                                alt="{{ $user->name }}"
                                class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700"
                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=18225E&background=E0E7FF&bold=true';"
                            />
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-slate-900 truncate dark:text-white">
                                    {{ $user->name }}
                                </div>
                                <div class="text-[11px] text-slate-500 truncate dark:text-slate-400">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium capitalize text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ $roleName }}
                            </span>
                            @if ($user->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                                    Pending
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Meta Informasi --}}
    <div class="grid grid-cols-2 gap-2 text-center text-[11px] text-slate-400 dark:text-slate-500">
        <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-2 dark:border-slate-800/50 dark:bg-slate-900/50">
            Dibuat: {{ $divisi->created_at ? $divisi->created_at->format('d M Y') : '-' }}
        </div>
        <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-2 dark:border-slate-800/50 dark:bg-slate-900/50">
            Diperbarui: {{ $divisi->updated_at ? $divisi->updated_at->format('d M Y, H:i') : '-' }}
        </div>
    </div>
</div>

