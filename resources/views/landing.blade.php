<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EditPro — Professional Photo Editor Online</title>
        <meta name="description" content="Transform your photos in seconds. Professional black & white conversion, smart resizing, and lossless compression — all in your browser.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root { --font-display: 'Plus Jakarta Sans', sans-serif; }
            body { font-family: 'Inter', sans-serif; }
            h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
            .gradient-text {
                background: linear-gradient(135deg, #a855f7 0%, #6366f1 50%, #3b82f6 100%);
                -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            }
            .glow-purple { box-shadow: 0 0 60px -15px rgba(168,85,247,0.5); }
            .glow-sm { box-shadow: 0 0 30px -10px rgba(168,85,247,0.4); }
            .glass {
                background: rgba(15,23,42,0.7);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255,255,255,0.06);
            }
            @keyframes float { 0%,100%{transform:translateY(0px);} 50%{transform:translateY(-12px);} }
            @keyframes pulse-glow { 0%,100%{opacity:.6;} 50%{opacity:1;} }
            .float { animation: float 6s ease-in-out infinite; }
            .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
            @keyframes gradient-shift {
                0%{background-position:0% 50%;} 50%{background-position:100% 50%;} 100%{background-position:0% 50%;}
            }
            .animated-gradient {
                background: linear-gradient(-45deg,#a855f7,#6366f1,#3b82f6,#06b6d4);
                background-size: 400% 400%;
                animation: gradient-shift 8s ease infinite;
            }
        </style>
    </head>
    <body class="antialiased bg-[#080c14] text-white selection:bg-purple-600 selection:text-white" x-data="{ scrolled: false, mobileMenu: false }" @scroll.window="scrolled = (window.pageYOffset > 40)">

        <!-- Navigation -->
        <nav class="fixed w-full z-50 transition-all duration-500"
             :class="scrolled ? 'py-3' : 'py-5'">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="glass rounded-2xl px-6 transition-all duration-500"
                     :class="scrolled ? 'shadow-2xl shadow-purple-900/20' : ''">
                    <div class="flex justify-between items-center h-16">
                        <!-- Logo -->
                        <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                            <div class="w-8 h-8 animated-gradient rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <span class="font-bold text-xl text-white tracking-tight">Edit<span class="gradient-text">Pro</span></span>
                        </a>

                        <!-- Nav Links (desktop) -->
                        <div class="hidden md:flex items-center gap-1">
                            <a href="#features" class="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">Features</a>
                            <a href="#demo" class="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">Demo</a>
                            <a href="#pricing" class="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">Pricing</a>
                            <a href="#faq" class="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">FAQ</a>
                        </div>

                        <!-- CTA -->
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm text-slate-300 hover:text-white transition-colors font-medium">Dashboard →</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white transition-colors font-medium hidden sm:block">Sign in</a>
                                <a href="{{ route('register') }}" class="animated-gradient text-white text-sm px-5 py-2.5 rounded-xl font-semibold transition-all hover:scale-105 hover:shadow-lg hover:shadow-purple-500/30 active:scale-95">
                                    Get Started Free
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            @include('components.landing.hero')
            @include('components.landing.features')
            @include('components.landing.before-after')
            @include('components.landing.pricing')
            @include('components.landing.faq')
        </main>

        @include('components.landing.footer')
        @include('components.toast')

    </body>
</html>
