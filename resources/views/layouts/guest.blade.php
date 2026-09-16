<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk') — Kasiro</title>
    <script>try{if(localStorage.getItem('kasiro-theme')==='dark')document.documentElement.classList.add('dark')}catch(e){}</script>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/custom-select.js') }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        // Seluruh teks (huruf maupun angka) memakai DM Sans
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"DM Sans"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        ink:      'rgb(var(--n-ink) / <alpha-value>)',
                        inksoft:  'rgb(var(--n-inksoft) / <alpha-value>)',
                        inkmuted: 'rgb(var(--n-inkmuted) / <alpha-value>)',
                        canvas:   'rgb(var(--n-canvas) / <alpha-value>)',
                        paper:    'rgb(var(--n-paper) / <alpha-value>)',
                        rule:     'rgb(var(--n-rule) / <alpha-value>)',
                        rulesoft: 'rgb(var(--n-rulesoft) / <alpha-value>)',
                        cream:    'rgb(var(--n-canvas) / <alpha-value>)',
                        cream2:   'rgb(var(--n-cream2) / <alpha-value>)',
                        primary: {50:'rgb(var(--p-50) / <alpha-value>)',100:'rgb(var(--p-100) / <alpha-value>)',200:'rgb(var(--p-200) / <alpha-value>)',300:'rgb(var(--p-300) / <alpha-value>)',400:'rgb(var(--p-400) / <alpha-value>)',500:'rgb(var(--p-500) / <alpha-value>)',600:'rgb(var(--p-600) / <alpha-value>)',700:'rgb(var(--p-700) / <alpha-value>)',800:'rgb(var(--p-800) / <alpha-value>)',900:'rgb(var(--p-900) / <alpha-value>)'},
                        success: {50:'rgb(var(--s-50) / <alpha-value>)',100:'rgb(var(--s-100) / <alpha-value>)',200:'rgb(var(--s-200) / <alpha-value>)',300:'rgb(var(--s-300) / <alpha-value>)',400:'rgb(var(--s-400) / <alpha-value>)',500:'rgb(var(--s-500) / <alpha-value>)',600:'rgb(var(--s-600) / <alpha-value>)',700:'rgb(var(--s-700) / <alpha-value>)',800:'rgb(var(--s-800) / <alpha-value>)',900:'rgb(var(--s-900) / <alpha-value>)'},
                        danger:  {50:'rgb(var(--d-50) / <alpha-value>)',100:'rgb(var(--d-100) / <alpha-value>)',200:'rgb(var(--d-200) / <alpha-value>)',300:'rgb(var(--d-300) / <alpha-value>)',400:'rgb(var(--d-400) / <alpha-value>)',500:'rgb(var(--d-500) / <alpha-value>)',600:'rgb(var(--d-600) / <alpha-value>)',700:'rgb(var(--d-700) / <alpha-value>)',800:'rgb(var(--d-800) / <alpha-value>)',900:'rgb(var(--d-900) / <alpha-value>)'},
                        warning: {50:'rgb(var(--w-50) / <alpha-value>)',100:'rgb(var(--w-100) / <alpha-value>)',200:'rgb(var(--w-200) / <alpha-value>)',300:'rgb(var(--w-300) / <alpha-value>)',400:'rgb(var(--w-400) / <alpha-value>)',500:'rgb(var(--w-500) / <alpha-value>)',600:'rgb(var(--w-600) / <alpha-value>)',700:'rgb(var(--w-700) / <alpha-value>)',800:'rgb(var(--w-800) / <alpha-value>)',900:'rgb(var(--w-900) / <alpha-value>)'},
                    },
                    boxShadow: {
                        'card': '0 1px 2px 0 rgb(var(--n-shadow) / 0.05)',
                        'pop':  '0 8px 24px -8px rgb(var(--n-shadow) / 0.35)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            /* LIGHT THEME — latar terang, foreground lebih gelap agar kontras */
            --n-ink: 2 6 23;
            --n-inksoft: 51 65 85;
            --n-inkmuted: 71 85 105;
            --n-canvas: 248 250 252;
            --n-paper: 255 255 255;
            --n-rule: 203 213 225;
            --n-rulesoft: 226 232 240;
            --n-cream2: 241 245 249;
            --n-shadow: 15 23 42;
            --c-ticket: #CBD5E1;
            --p-50: 239 246 255; --p-100: 219 234 254; --p-200: 191 219 254; --p-300: 147 197 253; --p-400: 96 165 250; --p-500: 37 99 235;
            --p-600: 29 78 216; --p-700: 30 64 175; --p-800: 30 58 138; --p-900: 23 37 84;
            --s-50: 236 253 245; --s-100: 209 250 229; --s-200: 167 243 208; --s-300: 110 231 183; --s-400: 52 211 153; --s-500: 16 185 129;
            --s-600: 4 120 87; --s-700: 6 95 70; --s-800: 6 78 59; --s-900: 4 47 46;
            --d-50: 254 242 242; --d-100: 254 226 226; --d-200: 254 202 202; --d-300: 252 165 165; --d-400: 248 113 113; --d-500: 239 68 68;
            --d-600: 185 28 28; --d-700: 153 27 27; --d-800: 127 29 29; --d-900: 105 24 24;
            --w-50: 255 251 235; --w-100: 254 243 199; --w-200: 253 230 138; --w-300: 252 211 77; --w-400: 251 191 36; --w-500: 245 158 11;
            --w-600: 180 83 9; --w-700: 146 64 14; --w-800: 120 53 15; --w-900: 105 47 13;
        }
        .dark {
            color-scheme: dark;
            --n-ink: 248 250 252;
            --n-inksoft: 203 213 225;
            --n-inkmuted: 148 163 184;
            --n-canvas: 15 23 42;
            --n-paper: 30 41 59;
            --n-rule: 51 65 85;
            --n-rulesoft: 39 52 73;
            --n-cream2: 39 52 73;
            --n-shadow: 0 0 0;
            --c-ticket: #334155;
            --p-50: 30 58 138; --p-100: 30 64 175; --p-200: 30 64 175; --p-300: 96 165 250; --p-400: 96 165 250; --p-500: 59 130 246;
            --p-600: 59 130 246; --p-700: 96 165 250; --p-800: 147 197 253; --p-900: 191 219 254;
            --s-50: 6 78 59; --s-100: 6 95 70; --s-200: 4 120 87; --s-300: 16 185 129; --s-400: 52 211 153; --s-500: 52 211 153;
            --s-600: 52 211 153; --s-700: 110 231 183; --s-800: 110 231 183; --s-900: 167 243 208;
            --d-50: 127 29 29; --d-100: 153 27 27; --d-200: 153 27 27; --d-300: 248 113 113; --d-400: 248 113 113; --d-500: 248 113 113;
            --d-600: 239 68 68; --d-700: 252 165 165; --d-800: 252 165 165; --d-900: 254 202 202;
            --w-50: 120 53 15; --w-100: 120 53 15; --w-200: 146 64 14; --w-300: 251 191 36; --w-400: 251 191 36; --w-500: 251 191 36;
            --w-600: 217 119 6; --w-700: 251 191 36; --w-800: 252 211 77; --w-900: 253 230 138;
        }
        [x-cloak] { display: none !important; }
        .tnum { font-variant-numeric: tabular-nums; font-feature-settings: "tnum" 1; }
        .ticket-dash { background-image: repeating-linear-gradient(to right, var(--c-ticket) 0, var(--c-ticket) 5px, transparent 5px, transparent 10px); }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex items-center justify-center p-4 font-sans text-ink bg-canvas antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>