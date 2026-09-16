@extends('layouts.app')

@section('title', __('ui.riwayat.title'))
@section('page-title', __('ui.riwayat.title'))

@php
    $jsLocale = app()->getLocale() === 'en' ? 'en-GB' : 'id-ID';
    $methodLabels = [
        'Tunai'     => __('ui.common.cash'),
        'Non Tunai' => __('ui.common.cashless'),
    ];
    $statusLabels = [
        'Lunas' => __('ui.common.paid'),
    ];
    // Tanggal dummy tabel riwayat ikut format bahasa aktif
    $dateMap = [
        '14 Agu 2024' => kasiro_tanggal('2024-08-14'),
        '13 Agu 2024' => kasiro_tanggal('2024-08-13'),
        '12 Agu 2024' => kasiro_tanggal('2024-08-12'),
    ];
@endphp

@section('content')
<div x-data="riwayatApp(@js(['locale' => $jsLocale, 'rupiah' => __('ui.common.rp') . ' ', 'methods' => $methodLabels, 'statuses' => $statusLabels, 'dateMap' => $dateMap]))">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.riwayat.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5"><span x-text="transaksi.length"></span> {{ __('ui.riwayat.subtitle_word') }} <span class="font-mono font-semibold text-ink tnum" x-text="formatRupiah(totalSemua)"></span></p>
        </div>
    </div>

    <!-- Filter -->
    <div class="flex flex-col lg:flex-row gap-2 mt-5">
        <div class="relative flex-1 lg:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="{{ __('ui.riwayat.search_ph') }}"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
        </div>
        <div class="flex items-center gap-2">
            <input type="date" x-model="dateFrom" class="px-2.5 py-2 rounded-lg border border-rule bg-paper font-mono text-[13px] tnum text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <span class="text-[11px] text-inkmuted">{{ __('ui.common.to') }}</span>
            <input type="date" x-model="dateTo" class="px-2.5 py-2 rounded-lg border border-rule bg-paper font-mono text-[13px] tnum text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
        </div>
        <select x-model="filterPayment" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <option value="">{{ __('ui.riwayat.all_methods') }}</option>
            <option value="Tunai">{{ __('ui.common.cash') }}</option>
            <option value="Non Tunai">{{ __('ui.common.cashless') }}</option>
        </select>
    </div>

    <!-- Tabel transaksi -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[760px]">
                <thead>
                    <tr class="border-b border-rulesoft bg-canvas/50">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.riwayat.col_id') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.time') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.riwayat.col_kasir') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.riwayat.col_total') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.method') }}</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.status') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rowsep">
                    <template x-for="item in filteredTransaksi" :key="item.id">
                        <tr class="hover:bg-cream2/60 transition-colors duration-150">
                            <td class="px-4 py-3 font-mono text-[13px] font-medium text-ink" x-text="item.id"></td>
                            <td class="px-4 py-3 font-mono text-[13px] text-inksoft tnum whitespace-nowrap" x-text="dateLabel(item.date) + ' · ' + item.time"></td>
                            <td class="px-4 py-3 text-[13px] text-inksoft" x-text="item.kasir"></td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-[13px] text-ink tnum" x-text="formatRupiah(item.total)"></td>
                            <td class="px-4 py-3">
                                <span class="text-[12px]" :class="item.method === 'Tunai' ? 'text-success-600' : 'text-warning-600'" x-text="js.methods[item.method] || item.method"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                      :class="item.status === 'Lunas' ? 'bg-success-50 text-success-700' : 'bg-cream2 text-inksoft'"
                                      x-text="js.statuses[item.status] || item.status"></span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openDetailModal(item)" class="text-[12px] text-primary-600 hover:text-primary-700 font-semibold transition-colors duration-150">{{ __('ui.common.detail') }}</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="filteredTransaksi.length === 0" x-cloak class="py-14 text-center">
            <p class="text-sm text-inkmuted">{{ __('ui.riwayat.empty') }}</p>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-t border-rulesoft">
            <p class="text-[12px] text-inkmuted">{{ __('ui.riwayat.showing_pre') }} <span x-text="filteredTransaksi.length"></span> {{ __('ui.riwayat.showing_mid') }} <span x-text="transaksi.length"></span> {{ __('ui.riwayat.showing_post') }}</p>
            <div class="flex items-center gap-1 text-[12px]">
                <button class="px-2 py-1 text-inkmuted hover:text-ink transition-colors duration-150">{{ __('ui.common.previous') }}</button>
                <span class="px-2 py-1 font-mono text-primary-700 bg-primary-50 rounded-md">1</span>
                <button class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150">2</button>
                <button class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150">3</button>
                <button class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150">{{ __('ui.common.next') }}</button>
            </div>
        </div>
    </div>

    <!-- Modal: Detail struk -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showDetailModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-sm w-full overflow-hidden" x-transition.scale.duration.150ms>
            <!-- Kepala struk -->
            <div class="px-5 py-4 text-center border-b border-dashed border-rule">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-inkmuted">Kasiro</p>
                <p class="font-mono text-[13px] font-semibold text-ink mt-1" x-text="detailItem?.id"></p>
                <p class="text-[11px] text-inkmuted mt-0.5" x-text="dateLabel(detailItem?.date) + ' · ' + detailItem?.time + ' {{ __('ui.riwayat.kasir_label') }} ' + detailItem?.kasir"></p>
            </div>

            <!-- Item -->
            <div class="px-5 py-4 space-y-2 min-h-[120px]" x-show="detailItem">
                <template x-for="order in detailItem?.items" :key="order.name">
                    <div class="flex items-baseline gap-2">
                        <span class="text-[13px] text-ink"><span class="font-mono text-inkmuted tnum" x-text="order.qty + '× '"></span><span x-text="order.name"></span></span>
                        <span class="flex-1 border-b border-dotted border-rule self-center translate-y-[-3px]"></span>
                        <span class="font-mono text-[13px] text-ink tnum" x-text="formatRupiah(order.price * order.qty)"></span>
                    </div>
                </template>
            </div>

            <div class="ticket-dash h-px"></div>

            <!-- Kaki struk -->
            <div class="px-5 py-4 space-y-1.5" x-show="detailItem">
                <div class="flex items-baseline justify-between">
                    <span class="text-[13px] font-semibold text-ink">{{ __('ui.common.total') }}</span>
                    <span class="font-mono font-bold text-lg text-ink tnum" x-text="formatRupiah(detailItem?.total)"></span>
                </div>
                <div x-show="detailItem?.method === 'Tunai'" class="flex items-baseline justify-between text-[12px] text-inkmuted">
                    <span>{{ __('ui.common.cash') }}</span>
                    <span class="font-mono tnum" x-text="formatRupiah(detailItem?.paid)"></span>
                </div>
                <div x-show="detailItem?.method === 'Tunai' && detailItem?.change > 0" class="flex items-baseline justify-between text-[12px] text-success-600">
                    <span>{{ __('ui.common.change') }}</span>
                    <span class="font-mono font-semibold tnum" x-text="formatRupiah(detailItem?.change)"></span>
                </div>
            </div>

            <div class="px-5 py-3.5 border-t border-rulesoft bg-canvas/60 flex items-center justify-between">
                <button @click="showDetailModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.close') }}</button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-ink hover:bg-[#334155] text-white text-[13px] font-semibold transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    {{ __('ui.riwayat.cetak') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function riwayatApp(js) {
    return {
        js: js,
        search: '',
        dateFrom: '',
        dateTo: '',
        filterPayment: '',
        showDetailModal: false,
        detailItem: null,
        transaksi: [
            { id: 'TRX-001', date: '14 Agu 2024', time: '14:32', kasir: 'Rina', total: 45000, method: 'Tunai', status: 'Lunas', paid: 50000, change: 5000, items: [{name: 'Nasi Goreng', qty: 2, price: 15000}, {name: 'Es Teh', qty: 2, price: 5000}, {name: 'Es Jeruk', qty: 1, price: 6000}] },
            { id: 'TRX-002', date: '14 Agu 2024', time: '13:15', kasir: 'Budi', total: 78000, method: 'Tunai', status: 'Lunas', paid: 100000, change: 22000, items: [{name: 'Capjay', qty: 3, price: 18000}, {name: 'Es Teh', qty: 3, price: 5000}, {name: 'Nasi Goreng', qty: 1, price: 15000}] },
            { id: 'TRX-003', date: '14 Agu 2024', time: '12:45', kasir: 'Rina', total: 120000, method: 'Non Tunai', status: 'Lunas', paid: 120000, change: 0, items: [{name: 'Nasi Goreng Spesial', qty: 4, price: 22000}, {name: 'Es Kopi', qty: 4, price: 8000}] },
            { id: 'TRX-004', date: '14 Agu 2024', time: '11:20', kasir: 'Budi', total: 34000, method: 'Tunai', status: 'Lunas', paid: 50000, change: 16000, items: [{name: 'Bakmi', qty: 1, price: 14000}, {name: 'Es Jeruk', qty: 2, price: 6000}, {name: 'Kopi Panas', qty: 2, price: 7000}] },
            { id: 'TRX-005', date: '14 Agu 2024', time: '10:05', kasir: 'Rina', total: 95000, method: 'Tunai', status: 'Lunas', paid: 100000, change: 5000, items: [{name: 'Soto Ayam', qty: 3, price: 17000}, {name: 'Nasi Goreng', qty: 2, price: 15000}, {name: 'Es Teh', qty: 4, price: 5000}] },
            { id: 'TRX-006', date: '13 Agu 2024', time: '19:30', kasir: 'Budi', total: 62000, method: 'Non Tunai', status: 'Lunas', paid: 62000, change: 0, items: [{name: 'Mie Goreng', qty: 2, price: 15000}, {name: 'Capjay', qty: 1, price: 18000}, {name: 'Jus Alpukat', qty: 2, price: 12000}] },
            { id: 'TRX-007', date: '13 Agu 2024', time: '18:15', kasir: 'Rina', total: 28000, method: 'Tunai', status: 'Lunas', paid: 30000, change: 2000, items: [{name: 'Nasi Goreng', qty: 1, price: 15000}, {name: 'Es Teh', qty: 2, price: 5000}, {name: 'Air Mineral', qty: 1, price: 4000}] },
            { id: 'TRX-008', date: '13 Agu 2024', time: '17:00', kasir: 'Budi', total: 156000, method: 'Tunai', status: 'Lunas', paid: 160000, change: 4000, items: [{name: 'Nasi Goreng Spesial', qty: 5, price: 22000}, {name: 'Es Jeruk', qty: 5, price: 6000}, {name: 'Es Teh', qty: 2, price: 5000}] },
            { id: 'TRX-009', date: '12 Agu 2024', time: '15:45', kasir: 'Rina', total: 89000, method: 'Tunai', status: 'Lunas', paid: 100000, change: 11000, items: [{name: 'Soto Ayam', qty: 2, price: 17000}, {name: 'Capjay', qty: 2, price: 18000}, {name: 'Es Kopi', qty: 3, price: 8000}, {name: 'Bakmi', qty: 1, price: 14000}] },
            { id: 'TRX-010', date: '12 Agu 2024', time: '14:20', kasir: 'Budi', total: 43000, method: 'Tunai', status: 'Lunas', paid: 50000, change: 7000, items: [{name: 'Bakmi', qty: 2, price: 14000}, {name: 'Es Teh', qty: 3, price: 5000}, {name: 'Kopi Panas', qty: 1, price: 7000}] },
        ],
        dateLabel(raw) {
            return this.js.dateMap[raw] || raw;
        },
        get totalSemua() {
            return this.transaksi.reduce((sum, item) => sum + item.total, 0);
        },
        get filteredTransaksi() {
            return this.transaksi.filter(item => {
                const matchSearch = item.id.toLowerCase().includes(this.search.toLowerCase()) || item.kasir.toLowerCase().includes(this.search.toLowerCase());
                const matchPayment = !this.filterPayment || item.method === this.filterPayment;
                return matchSearch && matchPayment;
            });
        },
        openDetailModal(item) {
            this.detailItem = item;
            this.showDetailModal = true;
        },
        formatRupiah(num) {
            return this.js.rupiah + Number(num).toLocaleString(this.js.locale);
        }
    }
}
</script>
@endpush
@endsection
