<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'EditPro') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @stack('head-scripts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0a0a0f] text-slate-300 selection:bg-violet-600 selection:text-white"
          x-data="{ sidebarOpen: false }">

        <div class="min-h-screen flex">

            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-50 w-56 bg-[#0f0f17] border-r border-white/5 flex flex-col
                          transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

                <!-- Logo -->
                <div class="flex items-center gap-2.5 h-16 px-5 border-b border-white/5">
                    <div class="w-7 h-7 rounded-lg bg-violet-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-white tracking-tight">EditPro</span>
                </div>

                <!-- Nav -->
                <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('dashboard') ? 'bg-violet-600/15 text-violet-400' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>

                    <div class="pt-4">
                        <p class="px-3 mb-1.5 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">Tools</p>

                        <a href="{{ route('dashboard') }}#upload"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-slate-400 hover:text-slate-200 hover:bg-white/5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>

                        <a href="{{ route('editor.print') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('editor.print') ? 'bg-violet-600/15 text-violet-400' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </a>
                    </div>
                </nav>

                <!-- User bottom -->
                <div class="p-3 border-t border-white/5">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-white/5 transition-colors text-left">
                            <div class="w-7 h-7 rounded-full bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-300 text-xs font-bold flex-shrink-0 overflow-hidden">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Storage::disk('public')->url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-500 truncate">Pro Plan</p>
                            </div>
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute bottom-full left-0 right-0 mb-1 bg-[#1a1a26] border border-white/10 rounded-xl shadow-xl overflow-hidden z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white transition-colors">Profile Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-xs text-red-400 hover:bg-white/5 hover:text-red-300 transition-colors border-t border-white/5">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile overlay -->
            <div x-show="sidebarOpen" x-transition.opacity
                 class="fixed inset-0 bg-black/70 z-40 lg:hidden"
                 @click="sidebarOpen = false"></div>

            <!-- Main -->
            <div class="flex-1 flex flex-col min-w-0">

                <!-- Topbar -->
                <header class="h-16 bg-[#0f0f17]/80 backdrop-blur border-b border-white/5 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <!-- Breadcrumb / page hint can go here -->
                    <div class="flex-1"></div>
                </header>

                <!-- Content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @include('components.toast')
    </body>
</html>
