@extends('layouts.app')

@section('title', __('ui.pengeluaran.title'))
@section('page-title', __('ui.pengeluaran.title'))

@section('content')
@php
    $sortOptions = __('ui.pengeluaran.sorts');
    $jsStrings = [
        'rupiah'  => __('ui.common.rp') . ' ',
        'locale'  => app()->getLocale() === 'en' ? 'en-GB' : 'id-ID',
    ];
@endphp
<div x-data="pengeluaranApp(@js($jsStrings))">

    <!-- ====== Buku kas: total sebagai angka utama ====== -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-inkmuted">{{ __('ui.pengeluaran.total_label') }}</p>
            <p class="font-mono font-bold text-[32px] leading-tight text-ink tnum mt-1" x-text="formatRupiah(totalPengeluaran)"></p>
            <p class="text-[13px] text-inksoft mt-2">
                {{ __('ui.pengeluaran.today') }} <span class="font-mono font-semibold text-danger-600 tnum" x-text="formatRupiah(todayTotal)"></span>
                &nbsp;·&nbsp; {{ __('ui.pengeluaran.month') }} <span class="font-mono font-semibold text-danger-600 tnum" x-text="formatRupiah(monthTotal)"></span>
            </p>
        </div>
        <button @click="openAddModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg shadow-card transition-colors duration-150 self-start lg:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('ui.pengeluaran.add') }}
        </button>
    </div>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <div class="mt-4 px-4 py-2.5 rounded-lg bg-success-50 border border-success-200 text-[13px] text-success-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 px-4 py-2.5 rounded-lg bg-danger-50 border border-danger-200 text-[13px] text-danger-700">{{ $errors->first() }}</div>
    @endif

    <!-- Filter -->
    <div class="flex flex-col sm:flex-row gap-2 mt-6">
        <div class="relative flex-1 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="{{ __('ui.pengeluaran.search_ph') }}"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
        </div>
        <select x-model="filterCategory" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <option value="">{{ __('ui.common.all_categories') }}</option>
            @foreach ($kategoriList as $kat)
                <option value="{{ $kat }}">{{ $kat }}</option>
            @endforeach
        </select>
        <select x-model="sortBy" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            @foreach ($sortOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <!-- Buku kas: tabel di kartu -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-rulesoft bg-canvas/50">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.date') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.description') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.category') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.pengeluaran.col_amount') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rowsep">
                    <template x-for="item in filteredPengeluaran" :key="item.id">
                        <tr class="group hover:bg-cream2/60 transition-colors duration-150">
                            <td class="px-4 py-3 font-mono text-[13px] text-inksoft tnum whitespace-nowrap" x-text="formatTanggal(item.date)"></td>
                            <td class="px-4 py-3 text-[14px] font-medium text-ink" x-text="item.description"></td>
                            <td class="px-4 py-3">
                                <span class="text-[12px]" :class="kategoriClass(item.category)" x-text="item.category"></span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-[14px] text-danger-600 tnum" x-text="'−' + formatRupiah(item.amount)"></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="openDeleteModal(item)" class="text-[12px] text-inksoft hover:text-danger-600 font-medium transition-colors duration-150">{{ __('ui.common.delete') }}</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="filteredPengeluaran.length === 0" x-cloak class="py-14 text-center">
            <p class="text-sm text-inkmuted" x-show="pengeluaran.length === 0">{{ __('ui.pengeluaran.empty_list') }}</p>
            <p class="text-sm text-inkmuted" x-show="pengeluaran.length > 0">{{ __('ui.pengeluaran.empty_search') }}</p>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-t border-rulesoft">
            <p class="text-[12px] text-inkmuted"><span x-text="filteredPengeluaran.length"></span> {{ __('ui.pengeluaran.footer_entries') }} <span class="font-mono font-semibold text-danger-600 tnum" x-text="formatRupiah(filteredTotal)"></span></p>
            <p class="text-[12px] text-inkmuted font-mono tnum">{{ __('ui.pengeluaran.footer_rows', ['count' => $expenses->count()]) }}</p>
        </div>
    </div>

    <!-- Modal: Catat Pengeluaran (form POST nyata ke server) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-md w-full overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-5 py-3.5 border-b border-rulesoft flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-ink">{{ __('ui.pengeluaran.add') }}</h3>
                <button @click="showModal = false" class="text-inkmuted hover:text-ink p-1 rounded-md hover:bg-cream2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.pengeluaran.store') }}" class="px-5 py-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.date') }} <span class="text-danger-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.description') }} <span class="text-danger-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="{{ __('ui.pengeluaran.ph_desc') }}" required maxlength="100"
                           class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.category') }} <span class="text-danger-500">*</span></label>
                        <select name="category" required class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                            <option value="">{{ __('ui.pengeluaran.choose') }}</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" @selected(old('category') === $kat)>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.pengeluaran.form_amount') }} <span class="text-danger-500">*</span></label>
                        <input type="number" name="amount" min="0" value="{{ old('amount') }}" placeholder="100000" required
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Hapus -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showDeleteModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-sm w-full p-6 text-center" x-transition.scale.duration.150ms>
            <h3 class="text-[15px] font-bold text-ink">{{ __('ui.pengeluaran.delete_title') }}</h3>
            <p class="text-[13px] text-inksoft mt-2">{{ __('ui.pengeluaran.delete_pre') }}<span x-text="deleteItem?.description"></span>{{ __('ui.pengeluaran.delete_mid') }}</p>
            <div class="flex items-center justify-center gap-2.5 mt-5">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                <button @click="confirmDelete()" class="px-4 py-2 rounded-lg bg-danger-600 hover:bg-danger-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.yes_delete') }}</button>
            </div>
        </div>
    </div>

    <!-- Form tersembunyi untuk submit DELETE -->
    <form x-ref="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
function pengeluaranApp(js) {
    const deleteUrl = @js(route('admin.pengeluaran.destroy', ['pengeluaran' => '__ID__']));
    return {
        search: '',
        filterCategory: '',
        sortBy: 'jumlah-besar',
        js: js,
        showModal: false,
        showDeleteModal: false,
        deleteItem: null,
        pengeluaran: @js($expenses),
        get filteredPengeluaran() {
            const items = this.pengeluaran.filter(item => {
                const matchSearch = item.description.toLowerCase().includes(this.search.toLowerCase());
                const matchCategory = !this.filterCategory || item.category === this.filterCategory;
                return matchSearch && matchCategory;
            });
            const collator = new Intl.Collator(this.js.locale);
            const sorts = {
                'jumlah-besar': (a, b) => b.amount - a.amount,
                'jumlah-kecil': (a, b) => a.amount - b.amount,
                'abjad':        (a, b) => collator.compare(a.description, b.description),
                'abjad-z-a':    (a, b) => collator.compare(b.description, a.description),
            };
            return items.sort(sorts[this.sortBy] || sorts['jumlah-besar']);
        },
        get filteredTotal() {
            return this.filteredPengeluaran.reduce((sum, item) => sum + item.amount, 0);
        },
        get totalPengeluaran() {
            return this.pengeluaran.reduce((sum, item) => sum + item.amount, 0);
        },
        get todayTotal() {
            const today = new Date().toISOString().split('T')[0];
            return this.pengeluaran.filter(p => p.date === today).reduce((sum, p) => sum + p.amount, 0);
        },
        get monthTotal() {
            const currentMonth = new Date().toISOString().slice(0, 7);
            return this.pengeluaran.filter(p => p.date.startsWith(currentMonth)).reduce((sum, p) => sum + p.amount, 0);
        },
        kategoriClass(cat) {
            const map = {
                'Operasional': 'text-success-600',
                'Bahan Baku':  'text-primary-600',
                'Utilitas':    'text-warning-600',
                'Sewa':        'text-danger-600',
            };
            return map[cat] || 'text-inkmuted';
        },
        formatTanggal(iso) {
            if (!iso) return '-';
            const d = new Date(iso + 'T00:00:00');
            if (isNaN(d)) return iso;
            return d.toLocaleDateString(this.js.locale, { day: '2-digit', month: 'short', year: 'numeric' });
        },
        openAddModal() {
            this.showModal = true;
        },
        openDeleteModal(item) {
            this.deleteItem = item;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            if (!this.deleteItem) return;
            this.$refs.deleteForm.action = deleteUrl.replace('__ID__', this.deleteItem.id);
            this.$refs.deleteForm.submit();
        },
        formatRupiah(num) {
            return this.js.rupiah + Number(num).toLocaleString(this.js.locale);
        }
    }
}
</script>
@endpush
@endsection
