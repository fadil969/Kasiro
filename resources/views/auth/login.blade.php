@extends('layouts.guest')

@section('title', __('ui.auth.page_title'))

@section('content')
@php $localeNow = app()->getLocale(); @endphp
<div class="w-full max-w-sm">

    <!-- Ganti bahasa (tamu) -->
    <div class="flex justify-end mb-2">
        <a href="{{ route('lang.switch', $localeNow === 'id' ? 'en' : 'id') }}"
           title="{{ __('ui.lang.label') }}"
           class="px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wide uppercase text-inksoft hover:text-ink bg-paper border border-rule transition-colors">{{ $localeNow === 'id' ? 'EN' : 'ID' }}</a>
    </div>

    <!-- Kartu login: putih, border tipis, shadow halus -->
    <div class="bg-paper border border-rule rounded-2xl shadow-pop overflow-hidden">
        <div class="px-8 pt-8 pb-6 text-center">
            {{-- Logo Kasiro: varian gelap untuk light theme, varian terang untuk dark theme --}}
            <img src="{{ asset('images/logo-dark.png') }}" alt="Logo Kasiro" class="h-12 w-auto dark:hidden mx-auto">
            <img src="{{ asset('images/logo-light.png') }}" alt="Logo Kasiro" class="h-12 w-auto hidden dark:block mx-auto">
            <h1 class="text-xl font-bold tracking-[0.08em] text-ink mt-4">KASIRO</h1>
            <p class="text-[12px] text-inkmuted mt-1">{{ __('ui.auth.tagline') }}</p>
        </div>

        <div class="ticket-dash h-px mx-8"></div>

        <form action="{{ route('login.attempt') }}" method="POST" class="px-8 py-6 space-y-4" x-data="{ showPassword: false }">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg bg-danger-50 border border-danger-100 px-3 py-2.5 text-[13px] text-danger-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="username" class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.username') }}</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="{{ __('ui.auth.username_ph') }}" required autofocus
                       class="w-full px-3 py-2.5 rounded-lg border border-rule bg-canvas/60 text-sm text-ink placeholder-inkmuted/60 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 focus:bg-paper transition-colors duration-150">
            </div>

            <div>
                <label for="password" class="block text-[13px] font-medium text-ink mb-1.5">{{ __('ui.common.password') }}</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required
                           class="w-full px-3 py-2.5 pr-11 rounded-lg border border-rule bg-canvas/60 text-sm text-ink placeholder-inkmuted/60 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 focus:bg-paper transition-colors duration-150">
                    <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? @js(__('ui.auth.show_pw')) : @js(__('ui.auth.hide_pw'))"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-inkmuted hover:text-ink p-1" tabindex="-1">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.125m3.537-1.623A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.054 10.054 0 01-2.926 4.607M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                {{ __('ui.auth.submit') }}
            </button>
        </form>

        <div class="ticket-dash h-px mx-8"></div>

        <div class="px-8 py-4 bg-cream2/60">
            <p class="text-center text-[12px] text-inkmuted">{{ __('ui.auth.forgot') }}</p>
        </div>
    </div>

    <p class="text-center text-[11px] text-inkmuted mt-4">{{ __('ui.auth.footer') }}</p>
</div>
@endsection
