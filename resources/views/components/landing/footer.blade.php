{{-- ═══════════════════════════════════════════════════════════════
     FOOTER  —  Premium redesign
═══════════════════════════════════════════════════════════════ --}}
<footer class="relative pt-20 pb-10 overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-purple-500/20 to-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Top CTA strip --}}
        <div class="relative rounded-3xl p-px mb-16 overflow-hidden">
            <div class="absolute inset-0 animated-gradient rounded-3xl opacity-60"></div>
            <div class="relative glass rounded-3xl px-8 py-12 text-center"
                 style="background:linear-gradient(135deg,rgba(26,14,46,0.95) 0%,rgba(15,23,42,0.95) 100%);">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                    Ready to transform your photos?
                </h2>
                <p class="text-slate-400 mb-8 max-w-lg mx-auto">Join thousands of creators using EditPro to produce stunning, print-ready photos in seconds.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('register') }}" class="animated-gradient text-white px-8 py-3.5 rounded-xl font-bold transition-all hover:scale-105 hover:shadow-xl hover:shadow-purple-500/30">
                        Start for Free — No Card Required
                    </a>
                    <a href="#features" class="glass border border-slate-700 hover:border-slate-500 text-white px-8 py-3.5 rounded-xl font-semibold transition-all hover:bg-white/5">
                        Learn More
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer links --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4 group">
                    <div class="w-8 h-8 animated-gradient rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="font-bold text-lg text-white">Edit<span class="gradient-text">Pro</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-5">Professional photo editing made simple, fast, and accessible for everyone.</p>
                <div class="flex gap-3">
                    <a href="#" class="w-8 h-8 glass rounded-lg border border-slate-700 hover:border-purple-500/50 flex items-center justify-center text-slate-500 hover:text-white transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 glass rounded-lg border border-slate-700 hover:border-purple-500/50 flex items-center justify-center text-slate-500 hover:text-white transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Product</h4>
                <ul class="space-y-2.5">
                    <li><a href="#features" class="text-sm text-slate-500 hover:text-white transition-colors">Features</a></li>
                    <li><a href="#pricing" class="text-sm text-slate-500 hover:text-white transition-colors">Pricing</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">API</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Changelog</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Company</h4>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">About Us</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Blog</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Careers</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4 uppercase tracking-wider">Legal</h4>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Cookie Policy</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-slate-800/60 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-slate-600 text-sm">&copy; {{ date('Y') }} EditPro. All rights reserved.</p>
            <p class="text-slate-700 text-xs">Built with ❤️ using Laravel &amp; Alpine.js</p>
        </div>

    </div>
</footer>
