@extends('layouts.app')

@section('title', __('ui.profile.title'))
@section('page-title', __('ui.profile.page_title'))

@section('content')
<div class="max-w-2xl">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-lg font-bold text-ink">{{ __('ui.profile.title') }}</h1>
        <p class="text-[13px] text-inkmuted mt-0.5">{{ __('ui.profile.subtitle') }}</p>
    </div>

    <!-- Identitas -->
    <div class="bg-paper border border-rule rounded-xl shadow-card p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-xl bg-primary-600 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-card">A</div>
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <p class="text-[15px] font-bold text-ink">Admin Kasiro</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-cream2 text-inksoft">{{ __('ui.profile.owner') }}</span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-success-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span> {{ __('ui.common.active') }}
                </span>
            </div>
            <p class="text-[12px] text-inkmuted mt-1">{{ __('ui.profile.joined') }}</p>
        </div>
    </div>

    <!-- ====== Informasi akun ====== -->
    <section class="mt-6">
        <h2 class="text-[13px] font-semibold text-inksoft mb-3">{{ __('ui.profile.account_info') }}</h2>
        <div class="bg-paper border border-rule rounded-xl shadow-card p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.profile.full_name') }}</label>
                    <input type="text" value="Admin Kasiro"
                           class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-ink focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.username') }}</label>
                    <input type="text" value="admin" readonly
                           class="w-full px-3 py-2 rounded-lg border border-rulesoft bg-cream2/70 font-mono text-sm text-inkmuted cursor-not-allowed">
                </div>
            </div>
            <div class="flex justify-end mt-5">
                <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-semibold rounded-lg transition-colors duration-150">
                    {{ __('ui.profile.save_changes') }}
                </button>
            </div>
        </div>
    </section>

    <!-- ====== Ubah password ====== -->
    <section class="mt-6" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        <h2 class="text-[13px] font-semibold text-inksoft mb-3">{{ __('ui.profile.change_password') }}</h2>
        <div class="bg-paper border border-rule rounded-xl shadow-card p-5 max-w-md">
            <div class="space-y-4">
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.profile.current_pw') }}</label>
                    <div class="relative">
                        <input :type="showCurrent ? 'text' : 'password'" placeholder="{{ __('ui.profile.ph_current') }}"
                               class="w-full px-3 py-2 pr-10 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        <button type="button" @click="showCurrent = !showCurrent" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-inkmuted hover:text-ink p-1" tabindex="-1">
                            <svg x-show="!showCurrent" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCurrent" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.125m3.537-1.623A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.054 10.054 0 01-2.926 4.607M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.profile.new_pw') }}</label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" placeholder="{{ __('ui.profile.ph_new') }}"
                               class="w-full px-3 py-2 pr-10 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        <button type="button" @click="showNew = !showNew" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-inkmuted hover:text-ink p-1" tabindex="-1">
                            <svg x-show="!showNew" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNew" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.125m3.537-1.623A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.054 10.054 0 01-2.926 4.607M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.profile.confirm_new') }}</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" placeholder="{{ __('ui.profile.ph_confirm') }}"
                               class="w-full px-3 py-2 pr-10 rounded-lg border border-rule bg-paper text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-inkmuted hover:text-ink p-1" tabindex="-1">
                            <svg x-show="!showConfirm" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirm" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.125m3.537-1.623A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.054 10.054 0 01-2.926 4.607M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 pt-1">
                    <p class="text-[12px] text-inkmuted">{{ __('ui.profile.pw_hint') }}</p>
                    <button class="px-4 py-2 bg-ink hover:bg-[#334155] text-white text-[13px] font-semibold rounded-lg transition-colors duration-150 shrink-0">
                        {{ __('ui.profile.update_pw') }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
