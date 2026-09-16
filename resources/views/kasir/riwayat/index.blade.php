@extends('layouts.app')

@section('title', __('ui.riwayat_kasir.title'))
@section('page-title', __('ui.nav.riwayat_saya'))

@php
    $js = [
        'locale'   => app()->getLocale() === 'en' ? 'en-GB' : 'id-ID',
        'rupiah'   => __('ui.common.rp') . ' ',
        'methods'  => ['Tunai' => __('ui.common.cash'), 'Non Tunai' => __('ui.common.cashless')],
    ];
@endphp

@section('content')
<div x-data="kasirRiwayatApp(@js($js))">

    <!-- Header + rekap shift -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.riwayat_kasir.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.riwayat_kasir.subtitle') }}</p>
        </div>
        <div class="flex items-stretch gap-3">
            <div class="bg-paper border border-rule rounded-xl shadow-card px-4 py-2.5 text-center min-w-[90px]">
                <p class="text-[11px] uppercase tracking-wide text-inkmuted font-semibold">{{ __('ui.riwayat_kasir.card_trx') }}</p>
                <p class="font-mono font-bold text-lg text-ink tnum mt-0.5" x-text="stats.count"></p>
            </div>
            <div class="bg-paper border border-rule rounded-xl shadow-card px-4 py-2.5 text-center min-w-[120px]">
                <p class="text-[11px] uppercase tracking-wide text-inkmuted font-semibold">{{ __('ui.riwayat_kasir.card_total') }}</p>
                <p class="font-mono font-bold text-lg text-success-600 tnum mt-0.5" x-text="formatRupiah(stats.total)"></p>
            </div>
            <div class="bg-paper border border-rule rounded-xl shadow-card px-4 py-2.5 text-center min-w-[110px]">
                <p class="text-[11px] uppercase tracking-wide text-inkmuted font-semibold">{{ __('ui.riwayat_kasir.card_avg') }}</p>
                <p class="font-mono font-bold text-lg text-ink tnum mt-0.5" x-text="formatRupiah(stats.avg)"></p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="flex flex-col sm:flex-row gap-2 mt-5">
        <div class="relative flex-1 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="{{ __('ui.riwayat_kasir.search_ph') }}"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
        </div>
        <select x-model="filterMethod" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <option value="">{{ __('ui.riwayat.all_methods') }}</option>
            <option value="Tunai">{{ __('ui.common.cash') }}</option>
            <option value="Non Tunai">{{ __('ui.common.cashless') }}</option>
        </select>
    </div>

    <!-- Daftar transaksi: baris ringkas di kartu -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="divide-y divide-rulesoft">
            <template x-for="item in filteredTransaksi" :key="item.id">
                <button @click="openDetailModal(item)" class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-cream2/60 transition-colors duration-150">
                    <span class="font-mono text-[13px] font-medium text-ink w-20 shrink-0" x-text="item.id"></span>
                    <span class="font-mono text-[13px] text-inkmuted tnum w-14 shrink-0" x-text="item.time"></span>
                    <span class="text-[12px] shrink-0 w-20" :class="item.method === 'Tunai' ? 'text-success-600' : 'text-warning-600'" x-text="js.methods[item.method] || item.method"></span>
                    <span class="flex-1 border-b border-dotted border-rule self-center"></span>
                    <span class="font-mono font-semibold text-[14px] text-ink tnum" x-text="formatRupiah(item.total)"></span>
                    <span class="text-[12px] text-primary-600 font-semibold w-12 text-right shrink-0">{{ __('ui.common.detail') }}</span>
                </button>
            </template>
        </div>

        <div x-show="filteredTransaksi.length === 0" x-cloak class="py-14 text-center">
            <p class="text-sm text-inkmuted">{{ __('ui.riwayat_kasir.empty') }}</p>
        </div>

        <div class="px-4 py-3 border-t border-rulesoft">
            <p class="text-[12px] text-inkmuted">{{ __('ui.riwayat_kasir.showing_pre') }} <span x-text="filteredTransaksi.length"></span> {{ __('ui.riwayat_kasir.showing_post') }}</p>
        </div>
    </div>

    <!-- Modal: Detail struk -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showDetailModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-sm w-full overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-5 py-4 text-center border-b border-dashed border-rule">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-inkmuted">Kasiro</p>
                <p class="font-mono text-[13px] font-semibold text-ink mt-1" x-text="detailItem?.id"></p>
                <p class="text-[11px] text-inkmuted mt-0.5" x-text="detailItem?.time"></p>
            </div>
            <div class="ticket-dash h-px"></div>
            <div class="px-5 py-4 space-y-2" x-show="detailItem">
                <template x-for="order in detailItem?.items" :key="order.name">
                    <div class="flex items-baseline gap-2">
                        <span class="text-[13px] text-ink"><span class="font-mono text-inkmuted tnum" x-text="order.qty + '× '"></span><span x-text="order.name"></span></span>
                        <span class="flex-1 border-b border-dotted border-rule self-center translate-y-[-3px]"></span>
                        <span class="font-mono text-[13px] text-ink tnum" x-text="formatRupiah(order.price * order.qty)"></span>
                    </div>
                </template>
            </div>
            <div class="ticket-dash h-px"></div>
            <div class="px-5 py-4 flex items-baseline justify-between" x-show="detailItem">
                <span class="text-[13px] font-semibold text-ink">{{ __('ui.common.total') }}</span>
                <span class="font-mono font-bold text-lg text-ink tnum" x-text="formatRupiah(detailItem?.total)"></span>
            </div>
            <div class="px-5 py-3.5 border-t border-rulesoft bg-canvas/60 flex justify-end">
                <button @click="showDetailModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function kasirRiwayatApp(js) {
    return {
        js: js,
        search: '',
        filterMethod: '',
        showDetailModal: false,
        detailItem: null,
        transaksi: [
            { id: 'TRX-042', time: '14:32', total: 45000, method: 'Tunai', items: [{name: 'Nasi Goreng', qty: 2, price: 15000}, {name: 'Es Teh', qty: 2, price: 5000}, {name: 'Es Jeruk', qty: 1, price: 6000}] },
            { id: 'TRX-041', time: '13:15', total: 78000, method: 'Tunai', items: [{name: 'Capjay', qty: 3, price: 18000}, {name: 'Es Teh', qty: 3, price: 5000}, {name: 'Nasi Goreng', qty: 1, price: 15000}] },
            { id: 'TRX-040', time: '12:45', total: 120000, method: 'Non Tunai', items: [{name: 'Nasi Goreng Spesial', qty: 4, price: 22000}, {name: 'Es Kopi', qty: 4, price: 8000}] },
            { id: 'TRX-039', time: '11:20', total: 34000, method: 'Tunai', items: [{name: 'Bakmi', qty: 1, price: 14000}, {name: 'Es Jeruk', qty: 2, price: 6000}, {name: 'Kopi Panas', qty: 2, price: 7000}] },
            { id: 'TRX-038', time: '10:05', total: 95000, method: 'Tunai', items: [{name: 'Soto Ayam', qty: 3, price: 17000}, {name: 'Nasi Goreng', qty: 2, price: 15000}, {name: 'Es Teh', qty: 4, price: 5000}] },
            { id: 'TRX-037', time: '09:30', total: 28000, method: 'Tunai', items: [{name: 'Nasi Goreng', qty: 1, price: 15000}, {name: 'Es Teh', qty: 2, price: 5000}, {name: 'Air Mineral', qty: 1, price: 4000}] },
        ],
        get filteredTransaksi() {
            return this.transaksi.filter(item => {
                const matchSearch = item.id.toLowerCase().includes(this.search.toLowerCase());
                const matchMethod = !this.filterMethod || item.method === this.filterMethod;
                return matchSearch && matchMethod;
            });
        },
        get stats() {
            const count = this.transaksi.length;
            const total = this.transaksi.reduce((sum, t) => sum + t.total, 0);
            const avg = count > 0 ? Math.round(total / count) : 0;
            return { count, total, avg };
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
