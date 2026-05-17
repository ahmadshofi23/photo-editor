{{-- ═══════════════════════════════════════════════════════════════
     PRICING SECTION  —  Premium redesign
═══════════════════════════════════════════════════════════════ --}}
<section id="pricing" class="relative py-28 overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-700/40 to-transparent"></div>
        <div class="absolute top-[20%] left-[50%] -translate-x-1/2 w-[800px] h-[400px] rounded-full opacity-30"
             style="background:radial-gradient(ellipse,rgba(99,102,241,0.15) 0%,transparent 70%);"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-20">
            <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-4 py-1.5 rounded-full mb-5">Pricing</span>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-5">
                Simple, <span class="gradient-text">transparent</span> pricing
            </h2>
            <p class="text-slate-400 max-w-lg mx-auto text-lg">Start free, upgrade when you need more power. No hidden fees.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto items-center">

            {{-- Free --}}
            <div class="glass rounded-3xl p-8 border border-slate-800 hover:border-slate-700 transition-all duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Free</h3>
                        <p class="text-xs text-slate-500">Perfect for occasional edits</p>
                    </div>
                </div>
                <div class="mb-8">
                    <span class="text-5xl font-extrabold text-white">$0</span>
                    <span class="text-slate-500 ml-1">/month</span>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach(['5 uploads per day', 'Max 5 MB file size', 'Standard processing', 'Basic tools'] as $f)
                    <li class="flex items-center gap-3 text-sm text-slate-400">
                        <svg class="w-4 h-4 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block w-full text-center glass border border-slate-700 hover:border-slate-500 text-white px-6 py-3 rounded-xl font-semibold transition-all hover:bg-white/5">
                    Get Started
                </a>
            </div>

            {{-- Pro (featured) --}}
            <div class="relative rounded-3xl p-px overflow-hidden hover:-translate-y-1 transition-all duration-300">
                {{-- Animated border gradient --}}
                <div class="absolute inset-0 animated-gradient rounded-3xl"></div>
                <div class="relative rounded-3xl p-8" style="background:linear-gradient(135deg,#1a0e2e 0%,#0f172a 60%);">
                    <div class="absolute top-0 right-0 bg-gradient-to-l from-purple-500 to-indigo-500 text-white text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-bl-2xl rounded-tr-3xl">
                        Most Popular
                    </div>

                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl animated-gradient flex items-center justify-center shadow-lg shadow-purple-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Pro</h3>
                            <p class="text-xs text-purple-300/70">For professionals &amp; creators</p>
                        </div>
                    </div>
                    <div class="mb-8">
                        <span class="text-5xl font-extrabold text-white">$9</span>
                        <span class="text-purple-300/60 ml-1">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(['Unlimited uploads', 'Max 50 MB file size', 'Priority fast processing', 'Batch operations', 'Print layout engine', 'API access'] as $f)
                        <li class="flex items-center gap-3 text-sm text-purple-100/80">
                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full text-center animated-gradient text-white px-6 py-3 rounded-xl font-bold transition-all hover:opacity-90 hover:shadow-xl hover:shadow-purple-500/30">
                        Upgrade to Pro
                    </a>
                </div>
            </div>

            {{-- Enterprise --}}
            <div class="glass rounded-3xl p-8 border border-slate-800 hover:border-slate-700 transition-all duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Enterprise</h3>
                        <p class="text-xs text-slate-500">Custom solutions for teams</p>
                    </div>
                </div>
                <div class="mb-8">
                    <span class="text-4xl font-extrabold text-white">Custom</span>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach(['Dedicated API access', 'Custom SLAs & uptime', 'Priority dedicated support', 'White-label options'] as $f)
                    <li class="flex items-center gap-3 text-sm text-slate-400">
                        <svg class="w-4 h-4 text-cyan-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <a href="#" class="block w-full text-center glass border border-cyan-500/30 hover:border-cyan-500/60 text-cyan-400 hover:text-cyan-300 px-6 py-3 rounded-xl font-semibold transition-all">
                    Contact Sales
                </a>
            </div>

        </div>
    </div>
</section>
