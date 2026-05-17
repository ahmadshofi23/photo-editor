{{-- ═══════════════════════════════════════════════════════════════
     HERO SECTION  —  Premium redesign
═══════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center pt-24 pb-20 overflow-hidden">

    {{-- ── Background atmosphere ── --}}
    <div class="absolute inset-0 pointer-events-none">
        {{-- Deep grid --}}
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image:linear-gradient(#a855f7 1px,transparent 1px),linear-gradient(90deg,#a855f7 1px,transparent 1px);background-size:48px 48px;"></div>

        {{-- Glow orbs --}}
        <div class="absolute top-[-200px] right-[-150px] w-[700px] h-[700px] rounded-full pulse-glow"
             style="background:radial-gradient(circle,rgba(168,85,247,0.18) 0%,transparent 70%);"></div>
        <div class="absolute bottom-[-100px] left-[-100px] w-[500px] h-[500px] rounded-full"
             style="background:radial-gradient(circle,rgba(59,130,246,0.15) 0%,transparent 70%);"></div>
        <div class="absolute top-[40%] left-[30%] w-[300px] h-[300px] rounded-full"
             style="background:radial-gradient(circle,rgba(99,102,241,0.1) 0%,transparent 70%);"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">

        {{-- ── Badge ── --}}
        <div class="flex justify-center mb-8">
            <div class="inline-flex items-center gap-2 glass rounded-full px-4 py-2 text-sm text-purple-300 border border-purple-500/20">
                <span class="w-2 h-2 bg-purple-400 rounded-full animate-pulse"></span>
                Now with AI-powered photo processing
                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>

        {{-- ── Headline ── --}}
        <div class="text-center mb-8">
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold tracking-tight leading-[1.05] mb-6">
                <span class="text-white block">Transform Photos</span>
                <span class="gradient-text block">In Milliseconds.</span>
            </h1>
            <p class="max-w-2xl mx-auto text-lg md:text-xl text-slate-400 leading-relaxed">
                Professional-grade B&amp;W conversion, smart resizing &amp; print cropping,
                and lossless compression — all powered by a lightning-fast Laravel engine.
            </p>
        </div>

        {{-- ── CTA Buttons ── --}}
        <div class="flex flex-col sm:flex-row justify-center gap-4 mb-16">
            <a href="{{ route('register') }}"
               class="group relative inline-flex items-center justify-center gap-2 animated-gradient text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/40 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Start Editing Free
            </a>
            <a href="#demo"
               class="inline-flex items-center justify-center gap-2 glass text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all hover:bg-white/10 hover:scale-105 active:scale-95">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Watch Demo
            </a>
        </div>

        {{-- ── Upload Dropzone ── --}}
        <div class="max-w-2xl mx-auto" x-data="uploadComponent()">
            <div
                @dragover.prevent="dragover = true"
                @dragleave.prevent="dragover = false"
                @drop.prevent="handleDrop"
                @click="$refs.fileInput.click()"
                :class="dragover ? 'border-purple-400 bg-purple-500/10 scale-[1.02]' : 'border-slate-700/60 hover:border-purple-500/50'"
                class="relative group glass border-2 border-dashed rounded-3xl p-10 transition-all duration-300 cursor-pointer">

                <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden" accept="image/*">

                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-2xl glass border border-slate-700 flex items-center justify-center group-hover:border-purple-500/50 group-hover:bg-purple-500/10 transition-all duration-300 float">
                        <svg class="w-8 h-8 text-slate-400 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-lg">Drop your photo here</p>
                        <p class="text-slate-500 text-sm mt-1">or click to browse · JPG, PNG, WebP up to 10 MB</p>
                    </div>
                    <div class="flex justify-center gap-4 text-xs text-slate-600">
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>B&amp;W Studio</span>
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>Smart Resize</span>
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Compress</span>
                    </div>
                </div>

                {{-- Uploading overlay --}}
                <div x-show="uploading" class="absolute inset-0 glass rounded-3xl flex flex-col items-center justify-center z-10" x-transition>
                    <div class="w-14 h-14 rounded-full animated-gradient flex items-center justify-center mb-4 animate-pulse">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <p class="text-white font-semibold">Uploading <span x-text="progress"></span>%</p>
                    <div class="w-48 h-1.5 bg-slate-700 rounded-full mt-3 overflow-hidden">
                        <div class="h-full animated-gradient rounded-full transition-all duration-300" :style="`width:${progress}%`"></div>
                    </div>
                </div>
            </div>

            {{-- Trust badges --}}
            <div class="flex justify-center items-center gap-6 mt-5 text-xs text-slate-600">
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Secure & Private</span>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Instant Processing</span>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>Free to Start</span>
            </div>
        </div>

    </div>
</section>

<script>
function uploadComponent() {
    return {
        dragover: false, uploading: false, progress: 0,
        handleDrop(e) { this.dragover = false; const f = e.dataTransfer.files; if (f.length) this.uploadFile(f[0]); },
        handleFileSelect(e) { const f = e.target.files; if (f.length) this.uploadFile(f[0]); },
        uploadFile(file) {
            if (!file.type.startsWith('image/')) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Please upload an image file.', type: 'error' } }));
                return;
            }
            this.uploading = true; this.progress = 0;
            const iv = setInterval(() => {
                this.progress += 10;
                if (this.progress >= 100) {
                    clearInterval(iv);
                    setTimeout(() => {
                        this.uploading = false;
                        @auth window.location.href = '/dashboard'; @else window.location.href = '{{ route("login") }}'; @endauth
                    }, 500);
                }
            }, 150);
        }
    }
}
</script>
