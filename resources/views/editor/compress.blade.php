<x-app-layout>
    <div x-data="compressEditor()" x-init="init()" class="h-[calc(100vh-10rem)] flex flex-col lg:flex-row gap-6">

        <!-- Left Panel: Image Preview -->
        <div class="w-full lg:w-2/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col overflow-hidden">

            <!-- Top bar -->
            <div class="h-14 border-b border-slate-700 flex items-center justify-between px-6 bg-slate-800/50 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <h2 class="text-white font-semibold">Smart Compress</h2>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="status === 'done'">
                        <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Compressed
                        </span>
                    </template>
                    <template x-if="status === 'processing'">
                        <span class="flex items-center gap-1.5 text-xs text-amber-400 font-medium">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Processing…
                        </span>
                    </template>
                </div>
            </div>

            <!-- Canvas: Before / After Slider -->
            <div class="flex-1 relative bg-slate-900/50 overflow-hidden"
                 @mousemove="sliderMove" @touchmove.prevent="sliderMove"
                 @mouseleave="dragging = false" @mouseup="dragging = false" @touchend="dragging = false">

                <div class="absolute inset-0" x-ref="canvas">
                    <!-- Original -->
                    <img src="{{ asset('storage/' . $image->original_path) }}"
                         class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                         alt="Original">

                    <!-- Compressed (clipped) -->
                    <div x-show="previewUrl"
                         class="absolute inset-0 overflow-hidden pointer-events-none"
                         :style="`clip-path: polygon(0 0, ${pos}% 0, ${pos}% 100%, 0 100%)`">
                        <img :src="previewUrl"
                             class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                             alt="Compressed">
                    </div>

                    <!-- Drag handle -->
                    <div x-show="previewUrl"
                         class="absolute inset-y-0 w-1 bg-emerald-500 z-10 cursor-ew-resize flex items-center justify-center shadow-[0_0_12px_rgba(16,185,129,0.6)]"
                         :style="`left: calc(${pos}% - 2px)`"
                         @mousedown="dragging = true" @touchstart="dragging = true">
                        <div class="w-8 h-8 bg-emerald-600 rounded-full border-2 border-white shadow-lg flex items-center justify-center pointer-events-auto">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l-4 4 4 4m8-8l4 4-4 4"></path></svg>
                        </div>
                    </div>

                    <!-- Labels -->
                    <div x-show="previewUrl" class="absolute bottom-4 left-4 text-xs text-white/70 bg-black/40 rounded px-2 py-1 pointer-events-none">Original</div>
                    <div x-show="previewUrl" class="absolute bottom-4 right-4 text-xs text-white/70 bg-black/40 rounded px-2 py-1 pointer-events-none">Compressed</div>

                    <!-- Processing overlay -->
                    <div x-show="status === 'processing'" class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm flex flex-col items-center justify-center z-20 gap-4">
                        <svg class="w-12 h-12 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-white text-sm font-medium">Compressing your image…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full lg:w-1/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col">
            <div class="p-6 flex-1 flex flex-col gap-6 overflow-y-auto">

                <div>
                    <h3 class="text-lg font-bold text-white mb-1">Smart Compress</h3>
                    <p class="text-sm text-slate-400">Your image is automatically compressed at optimal quality (80%). Drag the slider to compare before and after.</p>
                </div>

                <!-- Status card -->
                <div class="rounded-xl border p-4 transition-colors"
                     :class="status === 'done' ? 'border-emerald-500/40 bg-emerald-500/5' : status === 'error' ? 'border-red-500/40 bg-red-500/5' : 'border-slate-700 bg-slate-900/50'">
                    <template x-if="status === 'processing'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <p class="text-sm text-amber-300">Compressing your image…</p>
                        </div>
                    </template>
                    <template x-if="status === 'done'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <p class="text-sm text-emerald-300">Compression complete! Drag the slider to compare.</p>
                        </div>
                    </template>
                    <template x-if="status === 'error'">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="text-sm text-red-300 font-medium">Compression failed</p>
                                <p class="text-xs text-red-400 mt-1" x-text="errorMsg"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Compression stats -->
                <div x-show="status === 'done'" x-transition class="rounded-xl bg-slate-900/50 border border-emerald-500/30 p-4 space-y-3">
                    <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Compression Results</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Original size</span>
                        <span class="text-white font-mono" x-text="formatBytes(stats.original)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">New size</span>
                        <span class="text-emerald-400 font-mono font-bold" x-text="formatBytes(stats.new)"></span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-700">
                        <span class="text-slate-300 font-medium">Saved</span>
                        <span class="text-emerald-500 font-bold" x-text="stats.reduction + '%'"></span>
                    </div>
                    <!-- Progress bar -->
                    <div class="w-full bg-slate-700 rounded-full h-1.5 mt-1">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-700"
                             :style="`width: ${stats.reduction}%`"></div>
                    </div>
                </div>

                <!-- Image info -->
                <div class="rounded-xl bg-slate-900/50 border border-slate-700 p-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Image Info</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Filename</span>
                        <span class="text-white font-mono text-xs truncate max-w-[160px]">{{ pathinfo($image->original_path, PATHINFO_BASENAME) }}</span>
                    </div>
                    @if($image->size)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Original size</span>
                        <span class="text-white font-mono">{{ number_format($image->size / 1024, 1) }} KB</span>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Actions -->
            <div class="p-6 border-t border-slate-700 space-y-3 flex-shrink-0">
                <button @click="retryCompress"
                        x-show="status === 'error'"
                        class="w-full flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Retry
                </button>
                <button @click="downloadResult"
                        x-show="status === 'done'"
                        x-transition
                        class="w-full flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-600/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Compressed Image
                </button>
                <a href="{{ route('dashboard') }}"
                   class="w-full flex items-center justify-center px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-bold transition-all">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
    function compressEditor() {
        return {
            imageId: '{{ $image->id }}',
            status: 'processing', // processing | done | error
            previewUrl: '',
            errorMsg: '',
            pos: 50,
            dragging: false,

            stats: {
                original: {{ $image->size ?? 0 }},
                new: 0,
                reduction: 0,
            },

            init() {
                this.compress();
            },

            compress() {
                this.status = 'processing';
                this.errorMsg = '';
                axios.post('/api/v1/compress', {
                    image_id: this.imageId,
                    quality: 80,
                    convertWebp: false,
                }).then(res => {
                    // ApiResponse wraps data inside res.data.data
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        this.previewUrl = url + '?t=' + Date.now();
                        this.status = 'done';
                        const s = payload?.stats;
                        if (s) {
                            this.stats.original = s.original_size;
                            this.stats.new = s.new_size;
                            this.stats.reduction = parseFloat(s.reduction_percentage).toFixed(1);
                        }
                    } else {
                        throw new Error('No result URL returned.');
                    }
                }).catch(err => {
                    this.status = 'error';
                    this.errorMsg = err.response?.data?.message || err.message || 'Unknown error.';
                });
            },

            retryCompress() {
                this.compress();
            },

            sliderMove(e) {
                if (!this.dragging || !this.previewUrl) return;
                const rect = this.$refs.canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                this.pos = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
            },

            downloadResult() {
                axios.get(`/api/v1/download/${this.imageId}?type=edited`)
                    .then(res => {
                        const url = res.data?.data?.download_url || res.data?.download_url;
                        if (url) window.location.href = url;
                    }).catch(() => {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Download failed.', type: 'error' } }));
                    });
            },

            formatBytes(bytes, decimals = 1) {
                if (!+bytes) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
            },
        }
    }
    </script>
</x-app-layout>
