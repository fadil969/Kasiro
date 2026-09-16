@extends('layouts.app')

@php
    $posJs = [
        'locale'    => app()->getLocale() === 'en' ? 'en-GB' : 'id-ID',
        'rupiah'    => __('ui.common.rp') . ' ',
        'rupiah0'   => __('ui.common.rp') . ' 0',
        'pay_now'   => __('ui.pos.pay_now'),
        'pay_cashless' => __('ui.pos.pay_cashless'),
        'no_prefix' => __('ui.pos.no_prefix'),
    ];
@endphp

@section('title', __('ui.pos.title'))
@section('page-title', __('ui.pos.title'))

@section('content')
<div class="fixed left-0 right-0 top-14 bottom-0 lg:left-60 flex flex-col lg:flex-row"
     x-data="posApp(@js($posJs))" x-init="init()">

    <!-- ============ AREA MENU (kiri) ============ -->
    <div class="flex-1 flex flex-col min-w-0 bg-canvas">

        <!-- Search + kategori chip -->
        <div class="px-4 sm:px-6 py-3.5 border-b border-rule bg-canvas">
            <div class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" x-ref="search" placeholder="{{ __('ui.pos.search_ph') }}"
                           class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
                    <button @click="activeCategory = 'all'"
                            :class="activeCategory === 'all' ? 'bg-primary-600 text-white border-primary-600' : 'bg-paper text-inksoft border-rule hover:border-inkmuted/60'"
                            class="px-3.5 py-2.5 rounded-lg text-[13px] font-medium border transition-colors duration-150 whitespace-nowrap">{{ __('ui.pos.all') }}</button>
                    @foreach ($posCategories as $cat)
                        <button @click="activeCategory = @js($cat['value'])"
                                :class="activeCategory === @js($cat['value']) ? 'bg-primary-600 text-white border-primary-600' : 'bg-paper text-inksoft border-rule hover:border-inkmuted/60'"
                                class="px-3.5 py-2.5 rounded-lg text-[13px] font-medium border transition-colors duration-150 whitespace-nowrap">{{ $cat['label'] }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Grid menu: tombol besar bersih dengan qty badge -->
        <div class="flex-1 overflow-y-auto scrollbar-hide px-4 sm:px-6 py-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2.5 pb-6">
                <template x-for="item in filteredMenus" :key="item.id">
                    <button @click="addToCart(item)"
                            :class="getCartQty(item.id) > 0 ? 'bg-primary-50 border-primary-300 ring-1 ring-primary-400' : 'bg-paper border-rule hover:border-inkmuted/50 hover:shadow-card'"
                            class="relative text-left rounded-xl border p-3.5 transition-colors duration-150 group">
                        <div x-show="getCartQty(item.id) > 0" x-cloak x-transition.opacity.duration.150ms
                             class="absolute top-2.5 right-2.5 min-w-[22px] h-[22px] px-1 rounded-full bg-primary-600 text-white text-[12px] font-mono font-bold flex items-center justify-center"
                             x-text="getCartQty(item.id)"></div>
                        {{-- Foto menu: rasio konsisten, placeholder jika belum ada gambar --}}
                        <div class="w-full aspect-[16/10] rounded-lg overflow-hidden bg-canvas border border-rulesoft mb-2.5">
                            <template x-if="item.image">
                                <img :src="item.image" :alt="item.name" loading="lazy" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image">
                                <div class="w-full h-full flex items-center justify-center text-inkmuted/40">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>
                        {{-- Nama: maks 2 baris dengan tinggi tetap (2 line-height) supaya harga selalu sejajar antar kartu --}}
                        <p class="text-[15px] font-medium text-ink leading-snug pr-6 line-clamp-2 min-h-[2.625rem]" x-text="item.name"></p>
                        <p class="font-mono text-[13px] text-primary-600 font-semibold tnum mt-1.5" x-text="formatRupiah(item.price)"></p>
                    </button>
                </template>
            </div>
            <!-- Empty state pencarian -->
            <div x-show="filteredMenus.length === 0" x-cloak class="py-16 text-center">
                <p class="text-sm text-inkmuted">{{ __('ui.pos.no_match_pre') }}<span x-text="search"></span>"</p>
                <button @click="search = ''" class="mt-2 text-[13px] text-primary-600 hover:text-primary-700 font-medium">{{ __('ui.pos.clear_search') }}</button>
            </div>
        </div>
    </div>

    <!-- ============ NOTA / KERANJANG (kanan) ============ -->
    <div class="lg:w-[380px] shrink-0 bg-paper flex flex-col border-l border-rule
                max-lg:h-[55vh] max-lg:border-l-0 max-lg:border-t">

        <!-- Kepala nota -->
        <div class="px-5 pt-5 pb-4 shrink-0 border-b border-rulesoft">
            <div class="flex items-baseline justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-inkmuted">{{ __('ui.pos.nota_title') }}</p>
                <button @click="clearCart()" x-show="cart.length > 0" x-cloak
                        class="text-[12px] text-inkmuted hover:text-danger-600 transition-colors font-medium">{{ __('ui.pos.clear') }}</button>
            </div>
            <p class="font-mono text-[12px] text-inkmuted mt-1" x-text="nomorNota"></p>
        </div>

        <!-- Item nota -->
        <div class="flex-1 overflow-y-auto scrollbar-hide px-5 py-4 min-h-[100px]">
            <template x-if="cart.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center text-inkmuted">
                    <svg class="w-8 h-8 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 2L4 8v12a2 2 0 002 2h12a2 2 0 002-2V8l-5-6H9z"/>
                    </svg>
                    <p class="text-[13px]">{{ __('ui.pos.nota_empty1') }}</p>
                    <p class="text-[11px] mt-1">{{ __('ui.pos.nota_empty2') }}</p>
                </div>
            </template>

            <template x-for="item in cart" :key="item.id">
                <div class="py-2.5">
                    <div class="flex items-baseline gap-2">
                        <span class="text-[13px] font-medium text-ink truncate" x-text="item.name"></span>
                        <span class="flex-1 border-b border-dotted border-rule self-center translate-y-[-3px]"></span>
                        <span class="font-mono text-[13px] font-semibold text-ink tnum shrink-0" x-text="formatRupiah(item.price * item.qty)"></span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <button @click="decreaseQty(item.id)"
                                class="w-6 h-6 rounded-md border border-rule text-inksoft text-sm leading-none flex items-center justify-center hover:border-primary-400 hover:text-primary-600 transition-colors duration-150">−</button>
                        <span class="w-7 text-center font-mono text-[12px] font-semibold text-ink tnum" x-text="item.qty + '×'"></span>
                        <button @click="increaseQty(item.id)"
                                class="w-6 h-6 rounded-md border border-rule text-inksoft text-sm leading-none flex items-center justify-center hover:border-primary-400 hover:text-primary-600 transition-colors duration-150">+</button>
                        <button @click="removeItem(item.id)" class="ml-auto text-inkmuted/70 hover:text-danger-600 transition-colors p-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="ticket-dash h-px mx-5 shrink-0"></div>

        <!-- Kaki nota: total + bayar -->
        <div class="px-5 py-4 space-y-3.5 shrink-0 bg-paper">
            <div class="flex items-baseline justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-inkmuted">{{ __('ui.pos.total') }}</span>
                <span class="font-mono font-bold text-2xl text-ink tnum" x-text="formatRupiah(subtotal)"></span>
            </div>

            <!-- Metode: 2 tombol (Tunai / Non Tunai) -->
            <div class="grid grid-cols-2 gap-1.5">
                <button @click="paymentMethod = 'cash'"
                        :class="paymentMethod === 'cash' ? 'bg-primary-600 text-white border-primary-600' : 'bg-paper text-inksoft border-rule hover:border-inkmuted/60'"
                        class="py-2 rounded-lg text-[13px] font-semibold border transition-colors duration-150">{{ __('ui.common.cash') }}</button>
                <button @click="paymentMethod = 'nontunai'"
                        :class="paymentMethod === 'nontunai' ? 'bg-warning-600 text-white border-warning-600' : 'bg-paper text-inksoft border-rule hover:border-inkmuted/60'"
                        class="py-2 rounded-lg text-[13px] font-semibold border transition-colors duration-150">{{ __('ui.common.cashless') }}</button>
            </div>

            <!-- Tunai: uang diterima + kembalian -->
            <div x-show="paymentMethod === 'cash' && cart.length > 0" x-transition.opacity.duration.150ms x-cloak class="space-y-2">
                <div class="flex gap-1.5 flex-wrap">
                    <template x-for="amt in quickCash" :key="amt">
                        <button @click="cashPaid = amt"
                                :class="cashPaid === amt ? 'bg-primary-50 border-primary-400 text-primary-700' : 'border-rule text-inksoft hover:border-inkmuted/60'"
                                class="px-2.5 py-1 rounded-md border font-mono text-[11px] tnum transition-colors duration-150" x-text="formatRupiah(amt)"></button>
                    </template>
                </div>
                <div class="flex items-center gap-3">
                    <input type="number" min="0" x-model.number="cashPaid" @keydown="if ($event.key === '-') $event.preventDefault()" @input="if (Number(cashPaid) < 0) cashPaid = 0" placeholder="{{ __('ui.pos.cash_ph') }}"
                           class="flex-1 px-3 py-2 rounded-lg bg-canvas border border-rule font-mono font-semibold text-ink text-sm tnum placeholder:text-inkmuted/60 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div class="flex items-baseline justify-between" x-show="cashPaid > 0" x-cloak>
                    <span class="text-[12px] text-inkmuted">{{ __('ui.pos.change') }}</span>
                    <span class="font-mono font-bold tnum" :class="change >= 0 ? 'text-success-600' : 'text-danger-600'" x-text="formatRupiah(change)"></span>
                </div>
            </div>

            <!-- Non tunai -->
            <div x-show="paymentMethod === 'nontunai' && cart.length > 0" x-transition.opacity.duration.150ms x-cloak
                 class="text-[12px] text-inkmuted">
                {{ __('ui.pos.nontunai_note') }}
            </div>

            <!-- Tombol bayar -->
            <button @click="processPayment()"
                    :disabled="cart.length === 0 || (paymentMethod === 'cash' && change < 0)"
                    :class="cart.length === 0 || (paymentMethod === 'cash' && change < 0) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-primary-700'"
                    class="w-full py-2.5 bg-primary-600 text-white font-bold text-[15px] rounded-lg transition-all duration-150">
                <span x-text="paymentMethod === 'nontunai' ? js.pay_cashless : js.pay_now"></span>
            </button>
        </div>
    </div>

    <!-- ============ Modal sukses ============ -->
    <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showSuccessModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-xs w-full text-center overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-6 pt-7 pb-5">
                <div class="w-12 h-12 rounded-full bg-success-50 text-success-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-ink">{{ __('ui.pos.success') }}</h3>
                <p class="font-mono text-xl font-bold text-ink tnum mt-1.5" x-text="formatRupiah(subtotal)"></p>
                <div x-show="paymentMethod === 'cash' && change > 0" x-cloak class="mt-3 bg-success-50 rounded-lg p-3">
                    <p class="text-[11px] uppercase tracking-wide text-success-700 font-semibold">{{ __('ui.pos.change') }}</p>
                    <p class="font-mono font-bold text-success-700 tnum" x-text="formatRupiah(change)"></p>
                </div>
            </div>
            <div class="ticket-dash h-px"></div>
            <div class="p-4 bg-canvas/60">
                <button @click="showSuccessModal = false; clearCart(); cashPaid = 0;"
                        class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-lg transition-colors duration-150">
                    {{ __('ui.pos.new_nota') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posApp(js) {
    // Menu berasal dari database (lihat MenuController::kasir) — bukan localStorage.
    // Gambar tetap file lokal public/images/menu/*, dikirim sebagai URL oleh server.
    const serverMenus = @json($posMenus);

    return {
        js: js,
        search: '',
        activeCategory: 'all',
        paymentMethod: 'cash',
        cashPaid: 0,
        showSuccessModal: false,
        cart: [],
        nomorNota: '',
        quickCash: [20000, 50000, 100000],
        menus: serverMenus,
        get filteredMenus() {
            return this.menus.filter(item => {
                const matchSearch = item.name.toLowerCase().includes(this.search.toLowerCase());
                const matchCategory = this.activeCategory === 'all' || item.category === this.activeCategory;
                return matchSearch && matchCategory;
            });
        },
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },
        get change() {
            return this.cashPaid - this.subtotal;
        },
        addToCart(item) {
            const existing = this.cart.find(c => c.id === item.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({ ...item, qty: 1 });
            }
        },
        increaseQty(id) {
            const item = this.cart.find(c => c.id === id);
            if (item) item.qty++;
        },
        decreaseQty(id) {
            const item = this.cart.find(c => c.id === id);
            if (item) {
                if (item.qty > 1) {
                    item.qty--;
                } else {
                    this.removeItem(id);
                }
            }
        },
        removeItem(id) {
            this.cart = this.cart.filter(c => c.id !== id);
        },
        clearCart() {
            this.cart = [];
            this.cashPaid = 0;
        },
        getCartQty(id) {
            const item = this.cart.find(c => c.id === id);
            return item ? item.qty : 0;
        },
        formatRupiah(num) {
            if (num === null || num === undefined || isNaN(num)) return this.js.rupiah0;
            return this.js.rupiah + Number(num).toLocaleString(this.js.locale);
        },
        processPayment() {
            if (this.cart.length === 0) return;
            if (this.paymentMethod === 'cash' && this.change < 0) return;
            this.showSuccessModal = true;
        },
        init() {
            this.nomorNota = this.js.no_prefix + String(Math.floor(Math.random() * 900) + 100).padStart(3, '0');
            // Shortcut: "/" fokus ke pencarian, Escape menutup modal
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement !== this.$refs.search) {
                    e.preventDefault();
                    this.$refs.search.focus();
                }
                if (e.key === 'Escape') {
                    this.showSuccessModal = false;
                }
            });
        }
    }
}
</script>
@endpush
