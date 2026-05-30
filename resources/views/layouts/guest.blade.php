<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'EditPro') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0a0a0f] text-slate-300">
        <div class="min-h-screen flex">

            <!-- Left decorative panel -->
            <div class="hidden lg:flex lg:w-5/12 bg-[#0d0d16] border-r border-white/5 flex-col items-center justify-center p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-violet-900/25 via-transparent to-transparent pointer-events-none"></div>
                <div class="absolute inset-0 opacity-[0.025]"
                     style="background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px); background-size: 32px 32px;"></div>
                <div class="relative z-10 max-w-xs text-center">
                    <div class="w-12 h-12 rounded-2xl bg-violet-600 flex items-center justify-center mx-auto mb-6 shadow-xl shadow-violet-600/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-white mb-2 tracking-tight">EditPro</h1>
                    <p class="text-slate-500 text-sm leading-relaxed">Edit, resize, dan cetak foto dengan mudah dan profesional.</p>
                </div>
            </div>

            <!-- Right: form -->
            <div class="flex-1 flex items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-sm">
                    <div class="lg:hidden flex justify-center mb-8">
                        <div class="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center shadow-lg shadow-violet-600/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
