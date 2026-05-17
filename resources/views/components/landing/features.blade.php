{{-- ═══════════════════════════════════════════════════════════════
     FEATURES SECTION  —  Premium redesign
═══════════════════════════════════════════════════════════════ --}}
<section id="features" class="relative py-28 overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-purple-500/20 to-transparent"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-700/40 to-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="text-center mb-20">
            <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-purple-400 bg-purple-500/10 border border-purple-500/20 px-4 py-1.5 rounded-full mb-5">Powerful Tools</span>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-5 leading-tight">
                Everything you need,<br><span class="gradient-text">nothing you don't.</span>
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-lg">
                Three precision tools designed for speed and quality. No bloat, no learning curve.
            </p>
        </div>

        {{-- Feature cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- B&W Studio --}}
            <div class="group relative glass rounded-3xl p-8 border border-slate-800 hover:border-purple-500/40 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_30px_60px_-20px_rgba(168,85,247,0.25)] overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                     style="background:radial-gradient(ellipse at top left,rgba(168,85,247,0.07) 0%,transparent 70%);"></div>

                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-7 transition-all duration-300 bg-purple-500/10 border border-purple-500/20 group-hover:bg-purple-500/20 group-hover:border-purple-400/40">
                        <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-xl font-bold text-white">Black &amp; White Studio</h3>
                    </div>
                    <p class="text-slate-400 leading-relaxed mb-6">Professional-grade greyscale conversion with granular control over intensity, brightness, contrast, and sharpening.</p>

                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Fine-tuned intensity control
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Preserve highlights & shadows
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            One-click professional output
                        </div>
                    </div>
                </div>
            </div>

            {{-- Smart Resize --}}
            <div class="group relative glass rounded-3xl p-8 border border-slate-800 hover:border-blue-500/40 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_30px_60px_-20px_rgba(59,130,246,0.25)] overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                     style="background:radial-gradient(ellipse at top left,rgba(59,130,246,0.07) 0%,transparent 70%);"></div>

                <div class="absolute -top-3 -right-3">
                    <span class="bg-blue-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-bl-xl rounded-tr-3xl">Most Used</span>
                </div>

                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-7 transition-all duration-300 bg-blue-500/10 border border-blue-500/20 group-hover:bg-blue-500/20 group-hover:border-blue-400/40">
                        <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Smart Resize & Crop</h3>
                    <p class="text-slate-400 leading-relaxed mb-6">Resize for any platform instantly with smart presets. Crop, stretch, or fit while maintaining perfect aspect ratios for print.</p>

                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Photo presets (2x3, 3x4, 4x6…)
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Social media-ready sizes
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            300 DPI print-grade output
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compress --}}
            <div class="group relative glass rounded-3xl p-8 border border-slate-800 hover:border-emerald-500/40 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_30px_60px_-20px_rgba(16,185,129,0.25)] overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                     style="background:radial-gradient(ellipse at top left,rgba(16,185,129,0.07) 0%,transparent 70%);"></div>

                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-7 transition-all duration-300 bg-emerald-500/10 border border-emerald-500/20 group-hover:bg-emerald-500/20 group-hover:border-emerald-400/40">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Lossless Compression</h3>
                    <p class="text-slate-400 leading-relaxed mb-6">Reduce file size up to 80% without losing visible quality. Convert formats on the fly including modern WebP support.</p>

                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Up to 80% size reduction
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Convert JPG/PNG → WebP
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Adjustable quality slider
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-16">
            @foreach([['10K+','Photos processed'],['< 1s','Average processing'],['80%','Max compression'],['300','DPI print quality']] as $stat)
            <div class="glass rounded-2xl p-6 text-center border border-slate-800">
                <p class="text-3xl font-extrabold gradient-text mb-1">{{ $stat[0] }}</p>
                <p class="text-sm text-slate-500">{{ $stat[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
