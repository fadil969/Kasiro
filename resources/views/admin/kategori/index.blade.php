@extends('layouts.app')

@section('title', __('ui.kategori.title'))
@section('page-title', __('ui.kategori.page_title'))

@section('content')
<div x-data="kategoriApp({ errors: {{ $errors->any() ? 'true' : 'false' }}, mode: @js(old('_form_mode', 'add')), id: {{ old('id', 'null') }}, name: @js(old('name', '')), status: @js(old('status', 'Aktif')) })">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.kategori.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.kategori.subtitle', ['count' => $kategoris->count(), 'menus' => $totalMenu]) }}</p>
        </div>
        <button @click="openAddModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg shadow-card transition-colors duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('ui.kategori.add') }}
        </button>
    </div>

    <!-- Flash sukses / error -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-success-50 border border-success-100 px-3 py-2.5 text-[13px] text-success-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-4 rounded-lg bg-danger-50 border border-danger-100 px-3 py-2.5 text-[13px] text-danger-700">{{ session('error') }}</div>
    @endif

    <!-- Search -->
    <div class="relative max-w-xs mt-5">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" x-model="search" placeholder="{{ __('ui.kategori.search_ph') }}"
               class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
    </div>

    <!-- Daftar kategori: baris compact di kartu -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="divide-y divide-rowsep">
            @forelse ($kategoris as $kategori)
                <div class="flex items-center gap-4 px-5 py-3.5 group hover:bg-cream2/60 transition-colors duration-150"
                     x-show="'{{ strtolower(e($kategori->name)) }}'.includes(search.toLowerCase())">
                    <span class="w-6 h-6 rounded-md bg-cream2 text-inkmuted text-[11px] font-bold flex items-center justify-center shrink-0">{{ $loop->iteration }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-medium text-ink">{{ $kategori->name }}</p>
                        <p class="text-[12px] text-inkmuted mt-0.5">{{ __('ui.kategori.row_info', ['count' => $kategori->menus_count, 'date' => kasiro_tanggal($kategori->created_at)]) }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[12px] shrink-0 {{ $kategori->status === 'Aktif' ? 'text-success-600' : 'text-inkmuted' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $kategori->status === 'Aktif' ? 'bg-success-500' : 'bg-inkmuted/50' }}"></span>
                        {{ $kategori->status === 'Aktif' ? __('ui.common.active') : __('ui.common.inactive') }}
                    </span>
                    <div class="flex items-center gap-3 shrink-0 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-150">
                        <button @click="openEditModal({ id: {{ $kategori->id }}, name: @js($kategori->name), status: @js($kategori->status) })"
                                class="text-[12px] text-inksoft hover:text-primary-600 font-medium">{{ __('ui.common.edit') }}</button>
                        <button @click="openDeleteModal({ id: {{ $kategori->id }}, name: @js($kategori->name), count: {{ $kategori->menus_count }} })"
                                class="text-[12px] text-inksoft hover:text-danger-600 font-medium">{{ __('ui.common.delete') }}</button>
                    </div>
                </div>
            @empty
                <div class="py-14 text-center">
                    <p class="text-sm text-inkmuted">{{ __('ui.kategori.empty_list') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Empty state pencarian -->
        <div x-show="noResults()" x-cloak class="py-14 text-center">
            <p class="text-sm text-inkmuted">{{ __('ui.kategori.empty_search') }}</p>
        </div>
    </div>

    <!-- Modal: Tambah / Edit -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-md w-full overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-5 py-3.5 border-b border-rulesoft flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-ink" x-text="modalMode === 'add' ? @js(__('ui.kategori.add')) : @js(__('ui.kategori.edit'))"></h3>
                <button @click="showModal = false" class="text-inkmuted hover:text-ink p-1 rounded-md hover:bg-cream2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Tambah -->
            <form x-show="modalMode === 'add'" x-cloak action="{{ route('admin.kategori.store') }}" method="POST" class="contents">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kategori.form_name') }} <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" placeholder="{{ __('ui.kategori.ph_name') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('name') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.status') }}</label>
                        <select name="status" x-model="form.status" class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                            <option value="Aktif">{{ __('ui.common.active') }}</option>
                            <option value="Nonaktif">{{ __('ui.common.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3.5 border-t border-rulesoft flex items-center justify-end gap-2.5 bg-canvas/60">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.save') }}</button>
                </div>
            </form>

            <!-- Form Edit -->
            <form x-show="modalMode === 'edit'" x-cloak :action="'{{ url('admin/kategori') }}/' + editId" method="POST" class="contents">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="id" :value="editId">
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kategori.form_name') }} <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" x-model="form.name"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('name') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-inkmuted mt-1">{{ __('ui.kategori.rename_note') }}</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.status') }}</label>
                        <select name="status" x-model="form.status" class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                            <option value="Aktif">{{ __('ui.common.active') }}</option>
                            <option value="Nonaktif">{{ __('ui.common.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3.5 border-t border-rulesoft flex items-center justify-end gap-2.5 bg-canvas/60">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.update') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Hapus -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showDeleteModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-sm w-full p-6 text-center" x-transition.scale.duration.150ms>
            <h3 class="text-[15px] font-bold text-ink">{{ __('ui.kategori.delete_title') }}</h3>
            <p class="text-[13px] text-inksoft mt-2">
                <template x-if="deleteItem && deleteItem.count > 0">
                    <span>{{ __('ui.kategori.delete_used_pre') }}<span x-text="deleteItem?.name"></span>{{ __('ui.kategori.delete_used_mid') }}<span x-text="deleteItem?.count"></span>{{ __('ui.kategori.delete_used_tail') }}</span>
                </template>
                <template x-if="deleteItem && deleteItem.count === 0">
                    <span>{{ __('ui.kategori.delete_perm_pre') }}<span x-text="deleteItem?.name"></span>{{ __('ui.kategori.delete_perm_mid') }}</span>
                </template>
            </p>
            <form :action="'{{ url('admin/kategori') }}/' + (deleteItem?.id ?? 0)" method="POST" class="mt-5 flex items-center justify-center gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                <button type="submit" x-show="deleteItem && deleteItem.count === 0" x-cloak
                        class="px-4 py-2 rounded-lg bg-danger-600 hover:bg-danger-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.yes_delete') }}</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function kategoriApp(init) {
    return {
        search: '',
        showModal: init.errors === true,
        showDeleteModal: false,
        modalMode: init.mode === 'edit' ? 'edit' : 'add',
        deleteItem: null,
        editId: init.id ?? null,
        form: { name: init.name ?? '', status: init.status ?? 'Aktif' },
        openAddModal() {
            this.modalMode = 'add';
            this.form = { name: '', status: 'Aktif' };
            this.showModal = true;
        },
        openEditModal(item) {
            this.modalMode = 'edit';
            this.editId = item.id;
            this.form = { name: item.name, status: item.status };
            this.showModal = true;
        },
        openDeleteModal(item) {
            this.deleteItem = item;
            this.showDeleteModal = true;
        },
        noResults() {
            const rows = document.querySelectorAll('div.divide-y > div[x-show]');
            if (!rows.length) return false;
            return Array.from(rows).every(row => getComputedStyle(row).display === 'none');
        }
    }
}
</script>
@endpush
@endsection
