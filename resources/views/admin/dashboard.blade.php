@extends('layouts.app')

@section('title', __('ui.dashboard.title'))
@section('page-title', __('ui.dashboard.title'))

@section('content')
@php
    // ===== Data hari ini (dummy) =====
    $omzet = 1850000;
    $trxCount = 42;
    $laba = 720000;
    $pengeluaran = 150000;
    $avg = (int) round($omzet / $trxCount);

    // Singkatan 7 hari sesuai bahasa aktif (indeks 0 = Senin ... 5 = Sabtu)
    $chartDays = __('ui.dashboard.chart_days');
    $weekly = [
        ['d' => $chartDays[0], 'v' => 1200000], ['d' => $chartDays[1], 'v' => 1500000],
        ['d' => $chartDays[2], 'v' => 980000],  ['d' => $chartDays[3], 'v' => 1800000],
        ['d' => $chartDays[4], 'v' => 1450000], ['d' => $chartDays[5], 'v' => 2100000],
        ['d' => $chartDays[6], 'v' => 1650000],
    ];
    $maxWeek = max(array_column($weekly, 'v'));

    $bestMenus = [
        ['name' => 'Es Teh', 'sold' => 35, 'total' => 175000],
        ['name' => 'Nasi Goreng', 'sold' => 28, 'total' => 420000],
        ['name' => 'Capjay', 'sold' => 22, 'total' => 396000],
        ['name' => 'Es Jeruk', 'sold' => 20, 'total' => 120000],
        ['name' => 'Bakmi', 'sold' => 18, 'total' => 252000],
    ];

    $recentTrans = [
        ['id' => 'TRX-001', 'time' => '14:32', 'kasir' => 'Rina', 'total' => 45000, 'status' => 'paid'],
        ['id' => 'TRX-002', 'time' => '13:15', 'kasir' => 'Budi', 'total' => 78000, 'status' => 'paid'],
        ['id' => 'TRX-003', 'time' => '12:45', 'kasir' => 'Rina', 'total' => 120000, 'status' => 'paid'],
        ['id' => 'TRX-004', 'time' => '11:20', 'kasir' => 'Budi', 'total' => 34000, 'status' => 'paid'],
        ['id' => 'TRX-005', 'time' => '10:05', 'kasir' => 'Rina', 'total' => 95000, 'status' => 'paid'],
    ];
@endphp

<div class="max-w-6xl">

    <!-- ====== HERO: omset hari ini ====== -->
    <section>
        <div class="flex items-end justify-between gap-6 flex-wrap">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-inkmuted">{{ __('ui.dashboard.revenue_today') }}</p>
                <p class="font-mono font-bold text-[36px] sm:text-[40px] leading-tight text-ink tnum mt-1">{{ __('ui.common.rp') }} {{ number_format($omzet, 0, ',', '.') }}</p>
                <p class="text-[13px] text-inksoft mt-2.5">
                    <span class="text-success-600 font-semibold">▲ 12%</span> {{ __('ui.dashboard.vs_yesterday') }}
                    &nbsp;·&nbsp; {{ __('ui.dashboard.trx_count', ['n' => $trxCount]) }} &nbsp;·&nbsp; {{ __('ui.dashboard.average') }} <span class="font-mono font-semibold tnum whitespace-nowrap">{{ __('ui.common.rp') }} {{ number_format($avg, 0, ',', '.') }}</span>
                </p>
            </div>

            <!-- Aksi cepat -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="/kasir/transaksi" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg shadow-card transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('ui.dashboard.btn_new_trx') }}
                </a>
                <a href="/admin/pengeluaran" class="inline-flex items-center px-3.5 py-2 bg-paper hover:bg-cream2 text-ink text-[13px] font-semibold rounded-lg border border-rule transition-colors duration-150">{{ __('ui.dashboard.btn_record') }}</a>
                <a href="/admin/menu" class="inline-flex items-center px-3.5 py-2 bg-paper hover:bg-cream2 text-ink text-[13px] font-semibold rounded-lg border border-rule transition-colors duration-150">{{ __('ui.dashboard.btn_manage') }}</a>
            </div>
        </div>
    </section>

    <!-- ====== Kartu statistik ringkas ====== -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-stat-card :label="__('ui.dashboard.card_gross')" :value="__('ui.common.rp') . ' ' . number_format($laba, 0, ',', '.')" color="daun" :note="__('ui.dashboard.card_gross_note')" />
        <x-stat-card :label="__('ui.dashboard.card_exp')" :value="__('ui.common.rp') . ' ' . number_format($pengeluaran, 0, ',', '.')" color="chili" :note="__('ui.dashboard.card_exp_note', ['n' => 2])" />
        <x-stat-card :label="__('ui.dashboard.card_trx')" :value="__('ui.dashboard.trx_count', ['n' => $trxCount])" :note="__('ui.dashboard.card_trx_note', ['x' => __('ui.common.rp') . ' ' . number_format($avg, 0, ',', '.')])" />
    </section>

    <!-- ====== Dua kolom utama ====== -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-x-6 gap-y-6 mt-6">

        <!-- Kolom kiri: penjualan mingguan + transaksi terbaru -->
        <div class="lg:col-span-3 space-y-6">

            <!-- Penjualan mingguan (chart sederhana) -->
            <section class="bg-paper border border-rule rounded-xl shadow-card p-5">
                <div class="flex items-baseline justify-between mb-4">
                    <h2 class="text-[13px] font-semibold text-inksoft">{{ __('ui.dashboard.weekly_title') }}</h2>
                    <span class="text-[11px] text-inkmuted font-mono tnum">{{ __('ui.dashboard.weekly_total', ['x' => __('ui.common.rp') . ' 10,68 jt']) }}</span>
                </div>
                <div class="flex items-end gap-2 sm:gap-3 h-40">
                    @foreach($weekly as $di => $day)
                        @php $h = (int) round(($day['v'] / $maxWeek) * 130); @endphp
                        <div class="flex-1 flex flex-col items-center gap-1.5 group">
                            <span class="text-[10px] font-mono text-inkmuted opacity-0 group-hover:opacity-100 transition-opacity duration-150">{{ number_format($day['v'] / 1000, 0) }}k</span>
                            <div class="w-full bg-primary-600 group-hover:bg-primary-800 rounded-md transition-colors duration-150" style="height: {{ $h }}px"></div>
                            <span class="text-[11px] {{ $di === 5 ? 'text-ink font-semibold' : 'text-inkmuted' }}">{{ $day['d'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Transaksi terbaru -->
            <section class="bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-4 pb-3">
                    <h2 class="text-[13px] font-semibold text-inksoft">{{ __('ui.dashboard.recent_title') }}</h2>
                    <a href="/admin/riwayat" class="text-[13px] text-primary-600 hover:text-primary-700 font-medium">{{ __('ui.dashboard.see_all') }}</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-rowsep">
                        @foreach($recentTrans as $trx)
                        <tr class="hover:bg-cream2/60 transition-colors duration-150">
                            <td class="pl-5 pr-3 py-2.5 font-mono text-[13px] text-inkmuted whitespace-nowrap">{{ $trx['id'] }}</td>
                            <td class="px-3 py-2.5 text-[13px] text-inksoft whitespace-nowrap">{{ $trx['kasir'] }} · {{ $trx['time'] }}</td>
                            <td class="px-3 py-2.5 text-right font-mono font-semibold text-[13px] text-ink tnum whitespace-nowrap">{{ __('ui.common.rp') }} {{ number_format($trx['total'], 0, ',', '.') }}</td>
                            <td class="pl-3 pr-5 py-2.5 text-right w-20">
                                <x-badge variant="daun" :label="__('ui.common.status_paid')" />
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Kolom kanan: menu terlaris -->
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
                <div class="px-5 pt-4 pb-3">
                    <h2 class="text-[13px] font-semibold text-inksoft">{{ __('ui.dashboard.best_title') }}</h2>
                </div>
                <div class="divide-y divide-rulesoft">
                    @foreach($bestMenus as $i => $menu)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="w-6 h-6 rounded-md bg-cream2 text-inkmuted text-[11px] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-medium text-ink truncate">{{ $menu['name'] }}</p>
                            <p class="text-[11px] text-inkmuted mt-0.5">{{ __('ui.dashboard.sold', ['n' => $menu['sold']]) }}</p>
                        </div>
                        <span class="font-mono text-[13px] text-inksoft tnum whitespace-nowrap">{{ __('ui.common.rp') }} {{ number_format($menu['total'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
