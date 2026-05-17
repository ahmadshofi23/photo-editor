<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EditPro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Page-specific scripts that must load BEFORE Alpine.js starts -->
        @stack('head-scripts')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-900 text-slate-100 selection:bg-purple-600 selection:text-white" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen bg-slate-900 flex">
            
            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 border-r border-slate-700 transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto"
                   :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
                
                <div class="flex items-center justify-center h-20 border-b border-slate-700">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="font-bold text-xl text-white">EditPro</span>
                    </a>
                </div>

                <div class="p-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <div class="pt-4 mt-4 border-t border-slate-700">
                        <h4 class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tools</h4>
                        <a href="{{ route('dashboard') }}#upload" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors text-slate-400 hover:text-white hover:bg-slate-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Edit
                        </a>
                        <a href="{{ route('editor.print') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('editor.print') ? 'bg-indigo-600/20 text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Print
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Overlay for mobile sidebar -->
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden" @click="sidebarOpen = false"></div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Topbar -->
                <header class="h-20 bg-slate-800 border-b border-slate-700 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 sticky top-0">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <div class="flex-1 flex justify-end items-center gap-4">
                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 focus:outline-none">
                                <div class="w-10 h-10 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-white font-bold overflow-hidden">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ Storage::disk('public')->url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-400">Pro Plan</p>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            
                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-xl shadow-xl border border-slate-700 py-1 overflow-hidden z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">Profile Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @include('components.toast')
    </body>
</html>
