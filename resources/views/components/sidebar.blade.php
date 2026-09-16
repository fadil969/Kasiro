<!-- Sidebar KASIRO — putih bersih, item aktif = pill biru lembut -->
<aside class="fixed top-0 left-0 z-50 h-full w-60 bg-paper border-r border-rule flex flex-col transform transition-transform duration-200 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       x-cloak>

    <!-- Brand -->
    <div class="flex items-center gap-3 px-5 h-14 border-b border-rulesoft shrink-0">
        {{-- Logo Kasiro: varian gelap untuk light theme, varian terang untuk dark theme --}}
        <img src="{{ asset('images/logo-dark.png') }}" alt="Logo Kasiro" class="h-8 w-auto dark:hidden shrink-0">
        <img src="{{ asset('images/logo-light.png') }}" alt="Logo Kasiro" class="h-8 w-auto hidden dark:block shrink-0">
        <div class="min-w-0">
            <p class="text-[15px] font-bold tracking-[0.08em] text-ink leading-none">KASIRO</p>
            <p class="text-[10px] text-inkmuted mt-1 leading-none">{{ __('ui.app.tagline') }}</p>
        </div>
        <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-inkmuted hover:text-ink p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigasi -->
    <nav class="flex-1 overflow-y-auto scrollbar-hide px-3 py-4 space-y-5">
        @php
            // Deteksi area berdasarkan URL prefix
            $path = request()->path();
            $isKasirArea = str_starts_with($path, 'kasir');

            $adminGroups = [
                ['label' => null, 'items' => [
                    ['label' => __('ui.nav.dashboard'), 'route' => '/admin/dashboard',
                     'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                     'active' => request()->is('admin/dashboard')],
                ]],
                ['label' => __('ui.nav.management'), 'items' => [
                    ['label' => __('ui.nav.menu'), 'route' => '/admin/menu',
                     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                     'active' => request()->is('admin/menu')],
                    ['label' => __('ui.nav.kategori'), 'route' => '/admin/kategori',
                     'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                     'active' => request()->is('admin/kategori')],
                    ['label' => __('ui.nav.akun_kasir'), 'route' => '/admin/kasir',
                     'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                     'active' => request()->is('admin/kasir')],
                ]],
                ['label' => __('ui.nav.finance'), 'items' => [
                    ['label' => __('ui.nav.pengeluaran'), 'route' => '/admin/pengeluaran',
                     'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                     'active' => request()->is('admin/pengeluaran')],
                    ['label' => __('ui.nav.laporan'), 'route' => '/admin/laporan',
                     'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                     'active' => request()->is('admin/laporan')],
                    ['label' => __('ui.nav.riwayat'), 'route' => '/admin/riwayat',
                     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                     'active' => request()->is('admin/riwayat')],
                ]],
            ];

            $kasirGroups = [
                ['label' => null, 'items' => [
                    ['label' => __('ui.nav.transaksi_baru'), 'route' => '/kasir/transaksi',
                     'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
                     'active' => request()->is('kasir/transaksi')],
                ]],
                ['label' => __('ui.nav.operations'), 'items' => [
                    ['label' => __('ui.nav.riwayat_saya'), 'route' => '/kasir/riwayat',
                     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                     'active' => request()->is('kasir/riwayat')],
                ]],
            ];

            $groups = $isKasirArea ? $kasirGroups : $adminGroups;
            $authUser = auth()->user();
            $userName = $authUser?->name ?? ($isKasirArea ? 'Rina Susanti' : 'Admin Kasiro');
            $userRole = $authUser ? ($authUser->role === 'admin' ? __('ui.nav.owner') : __('ui.nav.cashier')) : ($isKasirArea ? __('ui.nav.cashier') : __('ui.nav.owner'));
            $userInitial = strtoupper(substr($userName, 0, 1));
        @endphp

        @foreach($groups as $group)
        <div>
            @if($group['label'])
            <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-inkmuted/70">{{ $group['label'] }}</p>
            @endif
            <div class="space-y-0.5">
                @foreach($group['items'] as $menu)
                <a href="{{ $menu['route'] }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ $menu['active'] ? 'bg-primary-50 text-primary-700' : 'text-inksoft hover:text-ink hover:bg-cream2' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $menu['active'] ? 'text-primary-600' : 'text-inkmuted' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $menu['icon'] }}"/>
                    </svg>
                    {{ $menu['label'] }}
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </nav>

    <!-- User -->
    <div class="shrink-0 px-3 py-3 border-t border-rulesoft">
        <div class="flex items-center gap-3 px-2 py-1.5">
            <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center text-xs font-bold shrink-0">{{ $userInitial }}</div>
            <div class="min-w-0 flex-1">
                <p class="text-[13px] font-medium text-ink truncate leading-tight">{{ $userName }}</p>
                <p class="text-[11px] text-inkmuted leading-none mt-1">{{ $userRole }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="contents">
                @csrf
                <button type="submit" title="{{ __('ui.topbar.logout') }}" class="p-1.5 text-inkmuted hover:text-danger-600 transition-colors rounded-md hover:bg-danger-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>