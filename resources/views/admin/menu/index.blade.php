@extends('layouts.app')

@php
    $sortOptions = __('ui.menu.sorts');
@endphp

@section('title', __('ui.menu.title'))
@section('page-title', __('ui.nav.menu'))

@section('content')
<div x-data="menuModal()">

    <!-- Header halaman: judul + aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.menu.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.menu.subtitle', ['count' => \App\Models\Menu::count(), 'aktif' => $totalAktif]) }}</p>
        </div>
        <button @click="openAdd()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg shadow-card transition-colors duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('ui.menu.add') }}
        </button>
    </div>

    <!-- Filter + urutan (server-side, method GET) -->
    <form method="GET" action="{{ route('admin.menu') }}" class="flex flex-col sm:flex-row gap-2 mt-5">
        <div class="relative flex-1 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.menu.search_ph') }}"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
        </div>
        <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <option value="">{{ __('ui.common.all_categories') }}</option>
            @foreach ($kategoris as $kategori)
                <option value="{{ $kategori->name }}" @selected(request('kategori') === $kategori->name)>{{ $kategori->name }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            <option value="">{{ __('ui.common.all_statuses') }}</option>
            <option value="Aktif" @selected(request('status') === 'Aktif')>{{ __('ui.common.active') }}</option>
            <option value="Nonaktif" @selected(request('status') === 'Nonaktif')>{{ __('ui.common.inactive') }}</option>
        </select>

        <select name="sort" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-inksoft focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
            @foreach ($sortOptions as $key => $label)
                <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button class="px-3.5 py-2 rounded-lg bg-primary-600 text-white text-[13px] font-semibold">{{ __('ui.common.apply') }}</button></noscript>
    </form>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <div class="mt-4 px-4 py-2.5 rounded-lg bg-success-50 border border-success-200 text-[13px] text-success-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 px-4 py-2.5 rounded-lg bg-danger-50 border border-danger-200 text-[13px] text-danger-700">{{ $errors->first() }}</div>
    @endif

    <!-- Tabel menu: max 5 baris per halaman -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[760px]">
                <thead>
                    <tr class="border-b border-rulesoft bg-canvas/50">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted w-10">{{ __('ui.menu.col_no') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.menu.col_menu') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.category') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.menu.col_price') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.menu.col_cost') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.menu.col_margin') }}</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.status') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rowsep">
                    @forelse ($menus as $menu)
                        <tr class="group hover:bg-cream2/60 transition-colors duration-150">
                            <td class="px-4 py-3 font-mono text-[12px] text-inkmuted tnum">{{ $loop->iteration + ($menus->currentPage() - 1) * $menus->perPage() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if ($menu->imageUrl())
                                        <img src="{{ $menu->imageUrl() }}" alt="{{ $menu->name }}" class="w-8 h-8 rounded-md object-cover border border-rulesoft shrink-0">
                                    @else
                                        <span class="w-8 h-8 rounded-md border border-rulesoft bg-canvas flex items-center justify-center text-inkmuted/40 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </span>
                                    @endif
                                    <span class="font-medium text-ink">{{ $menu->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[12px] {{ $menu->category === 'Makanan' ? 'text-primary-600' : 'text-success-600' }}">{{ $menu->category }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-ink tnum">Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-inkmuted tnum">Rp {{ number_format($menu->cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-[12px] text-success-600 tnum">+Rp {{ number_format($menu->price - $menu->cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.menu.toggle', $menu) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ __('ui.menu.toggle_title') }}"
                                            class="inline-flex items-center gap-1.5 text-[12px] {{ $menu->status === 'Aktif' ? 'text-success-600' : 'text-inkmuted' }} hover:opacity-80 transition-opacity">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $menu->status === 'Aktif' ? 'bg-success-500' : 'bg-inkmuted/50' }}"></span>
                                        {{ $menu->status === 'Aktif' ? __('ui.common.active') : __('ui.common.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" @click="openEdit({!! \Illuminate\Support\Js::from($menu) !!})" class="text-[12px] text-inksoft hover:text-primary-600 font-medium mr-3 transition-colors duration-150">{{ __('ui.common.edit') }}</button>
                                <button type="button" @click="askDelete({ id: {{ $menu->id }}, name: {{ \Illuminate\Support\Js::from($menu->name) }} })" class="text-[12px] text-inksoft hover:text-danger-600 font-medium transition-colors duration-150">{{ __('ui.common.delete') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center">
                                <p class="text-sm text-inkmuted">{{ __('ui.menu.empty1') }}</p>
                                <p class="text-[12px] text-inkmuted/70 mt-1">{{ __('ui.menu.empty2') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Kaki tabel: info + paginasi server-side -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 py-3 border-t border-rulesoft">
            <p class="text-[12px] text-inkmuted">
                {{ __('ui.menu.showing', ['a' => $menus->firstItem() ?? 0, 'b' => $menus->lastItem() ?? 0, 'total' => $menus->total()]) }}
            </p>
            @if ($menus->hasPages())
                <nav class="flex items-center gap-1 text-[12px]">
                    @if ($menus->onFirstPage())
                        <span class="px-2 py-1 text-inkmuted/40 cursor-not-allowed">{{ __('ui.common.previous') }}</span>
                    @else
                        <a href="{{ $menus->previousPageUrl() }}" class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150">{{ __('ui.common.previous') }}</a>
                    @endif

                    @for ($p = 1; $p <= $menus->lastPage(); $p++)
                        @if ($p === $menus->currentPage())
                            <span class="px-2 py-1 font-mono text-primary-700 bg-primary-50 rounded-md">{{ $p }}</span>
                        @else
                            <a href="{{ $menus->url($p) }}" class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150 font-mono">{{ $p }}</a>
                        @endif
                    @endfor

                    @if ($menus->hasMorePages())
                        <a href="{{ $menus->nextPageUrl() }}" class="px-2 py-1 text-inksoft hover:text-ink transition-colors duration-150">{{ __('ui.common.next') }}</a>
                    @else
                        <span class="px-2 py-1 text-inkmuted/40 cursor-not-allowed">{{ __('ui.common.next') }}</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>

    <!-- Modal: Tambah / Edit Menu (form POST ke server) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-lg w-full overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-5 py-3.5 border-b border-rulesoft flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-ink" x-text="mode === 'add' ? @js(__('ui.menu.add')) : @js(__('ui.menu.edit'))"></h3>
                <button @click="showModal = false" class="text-inkmuted hover:text-ink p-1 rounded-md hover:bg-cream2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form x-ref="form" :action="action" method="POST" enctype="multipart/form-data" class="px-5 py-4 space-y-4">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.menu.form_name') }} <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" :value="form.name" placeholder="{{ __('ui.menu.ph_name') }}" required
                           class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.category') }} <span class="text-danger-500">*</span></label>
                        <select name="category" class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                            <option value="">{{ __('ui.menu.choose_category') }}</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->name }}" :selected="form.category === {{ \Illuminate\Support\Js::from($kategori->name) }}">{{ $kategori->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.status') }}</label>
                        <select name="status" class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                            <option value="Aktif" :selected="form.status === 'Aktif'">{{ __('ui.common.active') }}</option>
                            <option value="Nonaktif" :selected="form.status === 'Nonaktif'">{{ __('ui.common.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.menu.form_price') }} <span class="text-danger-500">*</span></label>
                        <input type="number" name="price" min="0" step="500" :value="form.price" placeholder="15000" required
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.menu.form_cost') }}</label>
                        <input type="number" name="cost" min="0" step="500" :value="form.cost" placeholder="8000"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm tnum focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.menu.form_image') }}</label>
                    <div x-show="previewSrc" x-cloak class="mb-2">
                        <img :src="previewSrc" alt="{{ __('ui.menu.preview') }}" class="w-14 h-14 rounded-lg object-cover border border-rule">
                    </div>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif"
                           @change="previewFile($event)"
                           class="block w-full text-[13px] text-inksoft file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 file:text-[13px] file:font-semibold hover:file:bg-primary-100 border border-dashed border-rule rounded-lg bg-canvas/50">
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold transition-colors duration-150" x-text="mode === 'add' ? @js(__('ui.common.save')) : @js(__('ui.common.update'))"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Konfirmasi Hapus -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showDeleteModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-sm w-full p-6 text-center" x-transition.scale.duration.150ms>
            <h3 class="text-[15px] font-bold text-ink">{{ __('ui.menu.delete_title') }}</h3>
            <p class="text-[13px] text-inksoft mt-2">"<span x-text="deleteItem?.name"></span>" {{ __('ui.menu.delete_body') }}</p>
            <div class="flex items-center justify-center gap-2.5 mt-5">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                <button @click="confirmDelete()" class="px-4 py-2 rounded-lg bg-danger-600 hover:bg-danger-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.yes_delete') }}</button>
            </div>
        </div>
    </div>

    <!-- Form tersembunyi untuk submit DELETE -->
    <form x-ref="deleteForm" method="POST" id="menu-delete-form" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
function menuModal() {
    const baseUrl = {{ \Illuminate\Support\Js::from(route('admin.menu')) }};
    const msgImgSize = {{ \Illuminate\Support\Js::from(__('ui.menu.alert_img_size')) }};
    return {
        showModal: false,
        showDeleteModal: false,
        mode: 'add',
        action: baseUrl,
        form: { name: '', category: '', price: '', cost: '', status: 'Aktif' },
        deleteItem: null,
        previewSrc: '',
        openAdd() {
            this.mode = 'add';
            this.action = baseUrl;
            this.form = { name: '', category: '', price: '', cost: '', status: 'Aktif' };
            this.previewSrc = '';
            this.showModal = true;
            this.$nextTick(() => { this.$refs.form.querySelector('[name=image]').value = ''; });
        },
        openEdit(menu) {
            this.mode = 'edit';
            this.action = baseUrl + '/' + menu.id;
            this.form = { name: menu.name, category: menu.category, price: menu.price, cost: menu.cost, status: menu.status };
            this.previewSrc = '';
            this.showModal = true;
            this.$nextTick(() => { this.$refs.form.querySelector('[name=image]').value = ''; });
        },
        previewFile(event) {
            const file = event.target.files[0];
            if (!file) { this.previewSrc = ''; return; }
            if (file.size > 2 * 1024 * 1024) {
                alert(msgImgSize);
                event.target.value = '';
                this.previewSrc = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => { this.previewSrc = e.target.result; };
            reader.readAsDataURL(file);
        },
        askDelete(item) {
            this.deleteItem = item;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            if (!this.deleteItem) return;
            this.$refs.deleteForm.action = baseUrl + '/' + this.deleteItem.id;
            this.$refs.deleteForm.submit();
        }
    }
}
</script>
@endpush
@endsection
