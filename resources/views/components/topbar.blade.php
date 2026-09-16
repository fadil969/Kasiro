@php
    // Tanggal sesuai bahasa aktif (lihat helper kasiro_tanggal_panjang)
    $tanggalIndo = kasiro_tanggal_panjang();
    $localeNow = app()->getLocale();
@endphp

<header class="h-14 sticky top-0 z-30 bg-paper/90 backdrop-blur border-b border-rule flex items-center gap-3 px-4 sm:px-6 lg:px-8">
    <!-- Hamburger (mobile) -->
    <button @click="sidebarOpen = true" class="lg:hidden p-1.5 -ml-1.5 text-inksoft hover:text-ink rounded-md hover:bg-cream2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Brand kecil (mobile, saat sidebar tersembunyi) -->
    <span class="lg:hidden text-sm font-bold tracking-[0.08em] text-ink">KASIRO</span>

    <!-- Judul halaman -->
    <div class="flex items-baseline gap-2.5 min-w-0">
        <h1 class="text-[15px] font-semibold text-ink truncate">@yield('page-title', __('ui.topbar.dashboard'))</h1>
        <span class="hidden md:inline text-xs text-inkmuted whitespace-nowrap">{{ $tanggalIndo }}</span>
    </div>

    <div class="ml-auto flex items-center gap-1.5">
        <!-- Ganti bahasa — tepat di kiri tombol tema -->
        <a href="{{ route('lang.switch', $localeNow === 'id' ? 'en' : 'id') }}"
           title="{{ __('ui.lang.label') }}"
           class="px-2 py-1 rounded-md text-[11px] font-bold tracking-wide uppercase text-inksoft hover:text-ink hover:bg-cream2 border border-rule transition-colors">{{ $localeNow === 'id' ? 'EN' : 'ID' }}</a>

        <!-- Theme toggle -->
        <button @click="dark = !dark; localStorage.setItem('kasiro-theme', dark ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', dark)"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                class="p-2 rounded-md text-inksoft hover:text-ink hover:bg-cream2 transition-colors" title="{{ __('ui.app.change_theme') }}">
            <svg x-show="!dark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="dark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>

        <!-- Notifikasi -->
        @php $notifUser = auth()->user(); $isAdmin = ($notifUser->role ?? 'user') === 'admin'; @endphp
        <div class="relative" x-data="{ notifOpen: false }">
            <button @click="notifOpen = !notifOpen" class="relative p-2 rounded-md text-inksoft hover:text-ink hover:bg-cream2 transition-colors">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($isAdmin)
                <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-danger-500 rounded-full"></span>
                @endif
            </button>

            <div x-show="notifOpen" @click.away="notifOpen = false" x-transition.opacity.duration.150ms x-cloak
                 class="absolute right-0 mt-2 w-72 bg-paper border border-rule rounded-xl shadow-pop py-1.5 z-50">
                <p class="px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-inkmuted">{{ __('ui.topbar.notif') }}</p>
                @if($isAdmin)
                    <a href="{{ route('admin.laporan') }}" class="block px-4 py-2.5 hover:bg-cream2">
                        <p class="text-[13px] text-ink">{{ __('ui.topbar.notif_text') }}</p>
                        <p class="text-[11px] text-inkmuted mt-0.5">{{ __('ui.topbar.notif_time') }}</p>
                    </a>
                @else
                    <p class="px-4 py-3 text-[13px] text-inkmuted">{{ __('ui.topbar.notif_none') }}</p>
                @endif
            </div>
        </div>

        <span class="w-px h-5 bg-rulesoft mx-1"></span>

        <!-- Profil -->
        <div class="relative" x-data="{ profileOpen: false }">
            <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 p-1.5 rounded-md hover:bg-cream2 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-primary-600 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div class="hidden sm:block text-left leading-tight">
                    <p class="text-[13px] font-medium text-ink">{{ auth()->user()->name ?? 'Admin Kasiro' }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-inkmuted hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="profileOpen" @click.away="profileOpen = false" x-transition.opacity.duration.150ms x-cloak
                 class="absolute right-0 mt-2 w-44 bg-paper border border-rule rounded-xl shadow-pop py-1 z-50">
                <a href="/profile" class="block px-4 py-2 text-[13px] text-ink hover:bg-cream2">{{ __('ui.topbar.profile') }}</a>
                <div class="border-t border-rulesoft mt-1 pt-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-[13px] text-danger-600 hover:bg-danger-50">{{ __('ui.topbar.logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
