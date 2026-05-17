{{-- ═══════════════════════════════════════════════════════════════
     FAQ SECTION  —  Premium redesign
═══════════════════════════════════════════════════════════════ --}}
<section id="faq" class="relative py-28 overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-700/40 to-transparent"></div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-purple-400 bg-purple-500/10 border border-purple-500/20 px-4 py-1.5 rounded-full mb-5">FAQ</span>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4">
                Got <span class="gradient-text">questions?</span>
            </h2>
            <p class="text-slate-400 text-lg">We've got answers.</p>
        </div>

        <div class="space-y-3" x-data="{ active: null }">

            @php
            $faqs = [
                [1, 'Are my photos stored securely?', 'Yes, all uploaded photos are stored securely on our servers and are automatically deleted after 24 hours to protect your privacy. We never share your data with third parties.'],
                [2, 'What image formats do you support?', 'We currently support JPG, PNG, and WebP formats for upload. Our compress tool also allows you to convert from JPG/PNG to WebP on the fly for optimal web performance.'],
                [3, 'Can I cancel my Pro subscription anytime?', 'Absolutely. You can cancel your subscription at any time from your account settings with zero friction. You will retain Pro access until the end of your current billing cycle.'],
                [4, 'Is there a file size limit?', 'Free accounts can upload files up to 5 MB. Pro users can upload up to 50 MB per file, which covers even the highest-resolution RAW exports.'],
                [5, 'How accurate is the print sizing?', 'Our resize engine targets 300 DPI — the industry standard for print. The print layout preview shows exact centimetre dimensions so what you see is what you get.'],
            ];
            @endphp

            @foreach($faqs as [$idx, $q, $a])
            <div class="glass rounded-2xl border border-slate-800 overflow-hidden transition-all duration-300"
                 :class="active === {{ $idx }} ? 'border-purple-500/30' : 'hover:border-slate-700'">
                <button @click="active = active === {{ $idx }} ? null : {{ $idx }}"
                        class="w-full flex justify-between items-center p-6 text-left focus:outline-none group">
                    <span class="font-semibold text-white group-hover:text-purple-300 transition-colors pr-4">{{ $q }}</span>
                    <div class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center transition-all duration-300"
                         :class="active === {{ $idx }} ? 'bg-purple-500/20 text-purple-400 rotate-180' : 'bg-slate-700/50 text-slate-400'">
                        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>
                <div x-show="active === {{ $idx }}" x-collapse x-transition class="px-6 pb-6 text-slate-400 leading-relaxed border-t border-slate-800/50">
                    <p class="pt-4">{{ $a }}</p>
                </div>
            </div>
            @endforeach

        </div>

        {{-- CTA after FAQ --}}
        <div class="mt-16 text-center glass rounded-3xl p-10 border border-slate-800">
            <h3 class="text-2xl font-bold text-white mb-3">Still have questions?</h3>
            <p class="text-slate-400 mb-6">Our team is happy to help you get started.</p>
            <a href="mailto:support@editpro.app" class="inline-flex items-center gap-2 animated-gradient text-white px-7 py-3 rounded-xl font-semibold transition-all hover:scale-105 hover:shadow-lg hover:shadow-purple-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Support
            </a>
        </div>

    </div>
</section>
