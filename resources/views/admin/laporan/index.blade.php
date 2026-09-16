@extends('layouts.app')

@section('title', __('ui.laporan.title'))
@section('page-title', __('ui.laporan.title'))

@php
    // Singkat tanggal untuk data dummy tabel, mengikuti bahasa aktif
    $ledgerDates = ['2024-08-14', '2024-08-13', '2024-08-12', '2024-08-11', '2024-08-10', '2024-08-09', '2024-08-08'];
    $jsLaporan = [
        'locale'   => app()->getLocale() === 'en' ? 'en-GB' : 'id-ID',
        'rupiah'   => __('ui.common.rp') . ' ',
        'dates'    => array_map(fn ($d) => kasiro_tanggal($d), $ledgerDates),
    ];
@endphp

@section('content')
<div x-data="laporanApp(@js($jsLaporan))">

    <!-- ====== Header + tab periode ====== -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.laporan.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.laporan.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Tab periode -->
            <div class="flex items-center gap-1">
                <button @click="setPeriod('today')"
                        :class="period === 'today' ? 'border-primary-500 text-ink' : 'border-transparent text-inkmuted hover:text-inksoft'"
                        class="px-3 py-1.5 text-[13px] font-semibold border-b-2 transition-colors duration-150">{{ __('ui.laporan.tab_today') }}</button>
                <button @click="setPeriod('week')"
                        :class="period === 'week' ? 'border-primary-500 text-ink' : 'border-transparent text-inkmuted hover:text-inksoft'"
                        class="px-3 py-1.5 text-[13px] font-semibold border-b-2 transition-colors duration-150">{{ __('ui.laporan.tab_week') }}</button>
                <button @click="setPeriod('month')"
                        :class="period === 'month' ? 'border-primary-500 text-ink' : 'border-transparent text-inkmuted hover:text-inksoft'"
                        class="px-3 py-1.5 text-[13px] font-semibold border-b-2 transition-colors duration-150">{{ __('ui.laporan.tab_month') }}</button>
                <button @click="setPeriod('custom')"
                        :class="period === 'custom' ? 'border-primary-500 text-ink' : 'border-transparent text-inkmuted hover:text-inksoft'"
                        class="px-3 py-1.5 text-[13px] font-semibold border-b-2 transition-colors duration-150">{{ __('ui.laporan.tab_custom') }}</button>
            </div>
            <button class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-paper hover:bg-cream2 text-ink border border-rule text-[13px] font-semibold rounded-lg transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('ui.laporan.export') }}
            </button>
        </div>
    </div>

    <!-- Rentang custom -->
    <div x-show="period === 'custom'" x-transition.opacity.duration.150ms x-cloak class="flex items-center gap-2 mt-4">
        <input type="date" x-model="dateFrom" class="px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100">
        <span class="text-[12px] text-inkmuted">{{ __('ui.common.to') }}</span>
        <input type="date" x-model="dateTo" class="px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100">
        <span class="text-[12px] text-inkmuted" x-show="dateFrom && dateTo" x-text="'(' + periodLabel + ')'"></span>
    </div>

    <!-- ====== Ringkasan periode ====== -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-paper border border-rule rounded-xl shadow-card p-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-inkmuted">{{ __('ui.laporan.income') }}</p>
            <p class="font-mono font-bold text-[22px] leading-tight text-success-600 tnum mt-1.5" x-text="formatRupiah(stats.penjualan)"></p>
            <p class="text-[11px] text-inkmuted mt-1" x-text="trxCount + ' ' + js.income_trx + formatRupiah(stats.modal)"></p>
        </div>
        <div class="bg-paper border border-rule rounded-xl shadow-card p-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-inkmuted">{{ __('ui.laporan.expenses') }}</p>
            <p class="font-mono font-bold text-[22px] leading-tight text-danger-600 tnum mt-1.5" x-text="formatRupiah(stats.pengeluaran)"></p>
            <p class="text-[11px] text-inkmuted mt-1" x-text="expCount + ' ' + js.expenses_note"></p>
        </div>
        <div class="bg-paper border border-rule rounded-xl shadow-card p-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-inkmuted">{{ __('ui.laporan.net') }}</p>
            <p class="font-mono font-bold text-[22px] leading-tight tnum mt-1.5" :class="stats.labaBersih >= 0 ? 'text-ink' : 'text-danger-600'" x-text="formatRupiah(stats.labaBersih)"></p>
            <p class="text-[11px] text-inkmuted mt-1">
                {{ __('ui.laporan.net_note_pre') }} <span class="font-mono text-success-600 tnum" x-text="formatRupiah(stats.labaKotor)"></span> {{ __('ui.laporan.net_note_post') }}
            </p>
        </div>
    </div>

    <!-- ====== Buku-besar harian ====== -->
    <div class="mt-6">
        <h2 class="text-[13px] font-semibold text-inksoft mb-3">{{ __('ui.laporan.ledger') }}</h2>
        <div class="bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead>
                        <tr class="border-b border-rulesoft bg-canvas/50">
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.date') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.col_trx') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.col_sales') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.col_cost') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.col_gross') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.expenses') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.laporan.col_net') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rowsep">
                        <template x-for="(row, i) in tableData" :key="row.date">
                            <tr class="hover:bg-cream2/60 transition-colors duration-150">
                                <td class="px-4 py-3 font-mono text-[13px] text-inksoft tnum" x-text="js.dates[i]"></td>
                                <td class="px-4 py-3 text-right text-[13px] text-inksoft" x-text="row.transactions"></td>
                                <td class="px-4 py-3 text-right font-mono text-[13px] text-success-600 tnum" x-text="formatRupiah(row.penjualan)"></td>
                                <td class="px-4 py-3 text-right font-mono text-[13px] text-inkmuted tnum" x-text="formatRupiah(row.modal)"></td>
                                <td class="px-4 py-3 text-right font-mono text-[13px] text-success-600 tnum" x-text="formatRupiah(row.laba)"></td>
                                <td class="px-4 py-3 text-right font-mono text-[13px] text-danger-600 tnum" x-text="formatRupiah(row.pengeluaran)"></td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-[13px] tnum"
                                    :class="row.labaBersih >= 0 ? 'text-ink' : 'text-danger-600'"
                                    x-text="formatRupiah(row.labaBersih)"></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-rule bg-canvas/50">
                            <td class="px-4 py-3 text-[12px] font-semibold text-inkmuted uppercase tracking-wide" colspan="2">{{ __('ui.laporan.period_total') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-[13px] text-success-700 tnum" x-text="formatRupiah(sumPenjualan)"></td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-[13px] text-inkmuted tnum" x-text="formatRupiah(sumModal)"></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-[13px] text-success-700 tnum" x-text="formatRupiah(sumLaba)"></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-[13px] text-danger-700 tnum" x-text="formatRupiah(sumPengeluaran)"></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-[14px] text-ink tnum" x-text="formatRupiah(sumBersih)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Catatan pembacaan laporan -->
    <p class="mt-5 text-[12px] text-inkmuted">
        {{ __('ui.laporan.howto_pre') }}<span class="text-success-600 font-medium">{{ __('ui.laporan.howto_green') }}</span>{{ __('ui.laporan.howto_mid') }}<span class="text-danger-600 font-medium">{{ __('ui.laporan.howto_red') }}</span>{{ __('ui.laporan.howto_post') }}
    </p>
</div>

@push('scripts')
<script>
function laporanApp(js) {
    return {
        js: js,
        period: 'week',
        dateFrom: '',
        dateTo: '',
        trxCount: 42,
        expCount: 2,
        stats: { penjualan: 1850000, modal: 1130000, labaKotor: 720000, pengeluaran: 150000, labaBersih: 570000 },
        tableData: [
            { transactions: 42, penjualan: 1850000, modal: 1130000, laba: 720000, pengeluaran: 150000, labaBersih: 570000 },
            { transactions: 38, penjualan: 1620000, modal: 980000,  laba: 640000, pengeluaran: 100000, labaBersih: 540000 },
            { transactions: 45, penjualan: 2100000, modal: 1250000, laba: 850000, pengeluaran: 250000, labaBersih: 600000 },
            { transactions: 35, penjualan: 1450000, modal: 870000,  laba: 580000, pengeluaran: 0,      labaBersih: 580000 },
            { transactions: 28, penjualan: 980000,  modal: 600000,  laba: 380000, pengeluaran: 50000,  labaBersih: 330000 },
            { transactions: 50, penjualan: 2300000, modal: 1380000, laba: 920000, pengeluaran: 200000, labaBersih: 720000 },
            { transactions: 40, penjualan: 1750000, modal: 1050000, laba: 700000, pengeluaran: 150000, labaBersih: 550000 },
        ],
        get sumPenjualan() { return this.tableData.reduce((s, r) => s + r.penjualan, 0); },
        get sumModal()     { return this.tableData.reduce((s, r) => s + r.modal, 0); },
        get sumLaba()      { return this.tableData.reduce((s, r) => s + r.laba, 0); },
        get sumPengeluaran() { return this.tableData.reduce((s, r) => s + r.pengeluaran, 0); },
        get sumBersih()    { return this.tableData.reduce((s, r) => s + r.labaBersih, 0); },
        get periodLabel() {
            const f = (iso) => {
                const d = new Date(iso + 'T00:00:00');
                return d.toLocaleDateString(this.js.locale, { day: '2-digit', month: 'short' });
            };
            return f(this.dateFrom) + ' – ' + f(this.dateTo);
        },
        setPeriod(p) {
            this.period = p;
            const multipliers = { today: 0.15, week: 1, month: 3.5, custom: 1 };
            const m = multipliers[p] || 1;
            this.stats = {
                penjualan: Math.round(1850000 * m),
                modal: Math.round(1130000 * m),
                labaKotor: Math.round(720000 * m),
                pengeluaran: Math.round(150000 * m),
                labaBersih: Math.round(570000 * m),
            };
            this.trxCount = Math.round(42 * m);
            this.expCount = Math.max(1, Math.round(2 * m));
        },
        formatRupiah(num) {
            return this.js.rupiah + Number(num).toLocaleString(this.js.locale);
        }
    }
}
</script>
@endpush
@endsection
