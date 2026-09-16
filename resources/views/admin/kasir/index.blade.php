@extends('layouts.app')

@section('title', __('ui.kasir.title'))
@section('page-title', __('ui.kasir.page_title'))

@section('content')
<div x-data="kasirApp({ errors: {{ $errors->any() ? 'true' : 'false' }}, mode: @js(old('_form_mode', 'add')), id: {{ old('id', 'null') }}, name: @js(old('name', '')), username: @js(old('username', '')), status: @js(old('status', 'Aktif')) })">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-ink">{{ __('ui.kasir.title') }}</h1>
            <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.kasir.subtitle', ['count' => $kasirs->count(), 'aktif' => $kasirs->where('status', 'Aktif')->count()]) }}</p>
        </div>
        <button @click="openAddModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg shadow-card transition-colors duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('ui.kasir.add') }}
        </button>
    </div>

    <!-- Flash sukses -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-success-50 border border-success-100 px-3 py-2.5 text-[13px] text-success-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search -->
    <div class="relative max-w-xs mt-5">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-inkmuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" x-model="search" placeholder="{{ __('ui.kasir.search_ph') }}"
               class="w-full pl-9 pr-3 py-2 rounded-lg border border-rule bg-paper text-sm placeholder:text-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
    </div>

    <!-- Tabel kasir -->
    <div class="mt-4 bg-paper border border-rule rounded-xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-rulesoft bg-canvas/50">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.name') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.username') }}</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.status') }}</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.kasir.col_joined') }}</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-inkmuted">{{ __('ui.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rowsep">
                    @forelse ($kasirs as $kasir)
                        <tr class="hover:bg-cream2/60 transition-colors duration-150"
                            x-show="
                                '{{ strtolower(e($kasir->name)) }}'.includes(search.toLowerCase()) ||
                                '{{ strtolower(e($kasir->username ?? '')) }}'.includes(search.toLowerCase())">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center text-[11px] font-bold shrink-0">{{ strtoupper(substr($kasir->name, 0, 1)) }}</span>
                                    <span class="font-medium text-ink">{{ $kasir->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-[13px] text-inksoft">{{ $kasir->username ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('admin.kasir.toggle', $kasir) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[11px] font-semibold transition-colors duration-150 {{ $kasir->status === 'Aktif' ? 'bg-success-50 text-success-700 hover:bg-success-100' : 'bg-cream2 text-inkmuted hover:text-ink' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $kasir->status === 'Aktif' ? 'bg-success-500' : 'bg-inkmuted/50' }}"></span>
                                        {{ $kasir->status === 'Aktif' ? __('ui.common.active') : __('ui.common.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-inksoft">{{ kasiro_tanggal($kasir->created_at) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="openEditModal({
                                            id: {{ $kasir->id }},
                                            name: @js($kasir->name),
                                            username: @js($kasir->username ?? ''),
                                            status: @js($kasir->status)
                                        })" class="text-[12px] text-inksoft hover:text-primary-600 font-medium mr-3 transition-colors duration-150">{{ __('ui.common.edit') }}</button>
                                <button @click="openDeleteModal({
                                            id: {{ $kasir->id }},
                                            name: @js($kasir->name)
                                        })" class="text-[12px] text-inksoft hover:text-danger-600 font-medium transition-colors duration-150">{{ __('ui.common.delete') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-sm text-inkmuted">{{ __('ui.kasir.empty_list') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="noResults()" x-cloak class="py-14 text-center">
            <p class="text-sm text-inkmuted">{{ __('ui.kasir.empty_search') }}</p>
        </div>
    </div>

    <!-- Modal: Tambah / Edit -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.150ms>
        <div class="absolute inset-0 bg-ink/40" @click="showModal = false"></div>
        <div class="relative bg-paper rounded-2xl shadow-pop border border-rule max-w-md w-full overflow-hidden" x-transition.scale.duration.150ms>
            <div class="px-5 py-3.5 border-b border-rulesoft flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-ink" x-text="modalMode === 'add' ? @js(__('ui.kasir.add')) : @js(__('ui.kasir.edit'))"></h3>
                <button @click="showModal = false" class="text-inkmuted hover:text-ink p-1 rounded-md hover:bg-cream2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Tambah -->
            <form x-show="modalMode === 'add'" x-cloak action="{{ route('admin.kasir.store') }}" method="POST" class="contents">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kasir.form_fullname') }} <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" placeholder="{{ __('ui.kasir.ph_fullname') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.username') }} <span class="text-[11px] font-normal text-inkmuted">{{ __('ui.kasir.username_hint') }}</span></label>
                        <input type="text" name="username" x-model="form.username" placeholder="rinakasir"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('username') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.password') }} <span class="text-danger-500">*</span></label>
                        <input type="password" name="password" x-model="form.password" placeholder="{{ __('ui.kasir.ph_password') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('password') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kasir.confirm') }} <span class="text-danger-500">*</span></label>
                        <input type="password" name="password_confirmation" x-model="form.passwordConfirm" placeholder="{{ __('ui.kasir.ph_confirm') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                </div>
                <div class="px-5 py-3.5 border-t border-rulesoft flex items-center justify-end gap-2.5 bg-canvas/60">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.save') }}</button>
                </div>
            </form>

            <!-- Form Edit -->
            <form x-show="modalMode === 'edit'" x-cloak :action="'{{ url('admin/kasir') }}/' + editId" method="POST" class="contents">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="id" :value="editId">
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kasir.form_fullname') }} <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" x-model="form.name"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.username') }}</label>
                        <input type="text" name="username" x-model="form.username"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper font-mono text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('username') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kasir.new_password') }} <span class="text-[11px] font-normal text-inkmuted">{{ __('ui.kasir.password_hint') }}</span></label>
                        <input type="password" name="password" x-model="form.password" placeholder="{{ __('ui.kasir.ph_password') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        @error('password') <p class="text-[12px] text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.kasir.confirm_new') }}</label>
                        <input type="password" name="password_confirmation" x-model="form.passwordConfirm" placeholder="{{ __('ui.kasir.ph_confirm') }}"
                               class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
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
            <h3 class="text-[15px] font-bold text-ink">{{ __('ui.kasir.delete_title') }}</h3>
            <p class="text-[13px] text-inksoft mt-2">{{ __('ui.kasir.delete_pre') }}<span x-text="deleteItem?.name"></span>{{ __('ui.kasir.delete_mid') }}</p>
            <form :action="'{{ url('admin/kasir') }}/' + (deleteItem?.id ?? 0)" method="POST" class="mt-5 flex items-center justify-center gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-lg border border-rule bg-paper text-ink text-[13px] font-medium hover:bg-cream2 transition-colors duration-150">{{ __('ui.common.cancel') }}</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-danger-600 hover:bg-danger-700 text-white text-[13px] font-semibold transition-colors duration-150">{{ __('ui.common.yes_delete') }}</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function kasirApp(init) {
    return {
        search: '',
        showModal: init.errors === true,
        showDeleteModal: false,
        modalMode: init.mode === 'edit' ? 'edit' : 'add',
        deleteItem: null,
        editId: init.id ?? null,
        form: { name: init.name ?? '', username: init.username ?? '', password: '', passwordConfirm: '', status: init.status ?? 'Aktif' },
        openAddModal() {
            this.modalMode = 'add';
            this.form = { name: '', username: '', password: '', passwordConfirm: '', status: 'Aktif' };
            this.showModal = true;
        },
        openEditModal(item) {
            this.modalMode = 'edit';
            this.editId = item.id;
            this.form = { name: item.name, username: item.username, password: '', passwordConfirm: '', status: item.status };
            this.showModal = true;
        },
        openDeleteModal(item) {
            this.deleteItem = item;
            this.showDeleteModal = true;
        },
        noResults() {
            const rows = document.querySelectorAll('tbody tr[x-show]');
            if (!rows.length) return false;
            return Array.from(rows).every(tr => tr.style.display === 'none' || getComputedStyle(tr).display === 'none');
        }
    }
}
</script>
@endpush
@endsection
