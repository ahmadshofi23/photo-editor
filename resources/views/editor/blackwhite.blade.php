<x-app-layout>
    <div x-data="bwEditor()" x-init="init()" class="h-[calc(100vh-10rem)] flex flex-col lg:flex-row gap-6">

        <!-- Left Panel: Image Preview -->
        <div class="w-full lg:w-2/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col overflow-hidden">

            <!-- Top bar -->
            <div class="h-14 border-b border-slate-700 flex items-center justify-between px-6 bg-slate-800/50 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <h2 class="text-white font-semibold">Black & White</h2>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="status === 'done'">
                        <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Converted
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
                    <!-- Image -->
                    <img :src="previewUrl"
                         class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                         alt="Preview">

                    <!-- Processing overlay -->
                    <div x-show="status === 'processing'" class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm flex flex-col items-center justify-center z-20 gap-4">
                        <svg class="w-12 h-12 text-purple-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-white text-sm font-medium">Converting to Black & White…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Info + Actions -->
        <div class="w-full lg:w-1/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col">
            <div class="p-6 flex-1 flex flex-col gap-6">

                <div>
                    <h3 class="text-lg font-bold text-white mb-1">Black & White</h3>
                    <p class="text-sm text-slate-400">Your image is automatically converted to grayscale.</p>
                </div>

                <!-- Status card -->
                <div class="rounded-xl border p-4 transition-colors"
                     :class="status === 'done' ? 'border-emerald-500/40 bg-emerald-500/5' : status === 'error' ? 'border-red-500/40 bg-red-500/5' : 'border-slate-700 bg-slate-900/50'">
                    <template x-if="status === 'processing'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <p class="text-sm text-amber-300">Processing your image…</p>
                        </div>
                    </template>
                    <template x-if="status === 'done'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <p class="text-sm text-emerald-300">Conversion complete!</p>
                        </div>
                    </template>
                    <template x-if="status === 'error'">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="text-sm text-red-300 font-medium">Conversion failed</p>
                                <p class="text-xs text-red-400 mt-1" x-text="errorMsg"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Image info -->
                <div class="rounded-xl bg-slate-900/50 border border-slate-700 p-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Image Info</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Filename</span>
                        <span class="text-white font-mono text-xs truncate max-w-[160px]">{{ pathinfo($image->original_path, PATHINFO_BASENAME) }}</span>
                    </div>
                    @if($image->width && $image->height)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Dimensions</span>
                        <span class="text-white font-mono">{{ $image->width }} × {{ $image->height }}</span>
                    </div>
                    @endif
                    @if($image->size)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Size</span>
                        <span class="text-white font-mono">{{ number_format($image->size / 1024, 1) }} KB</span>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Actions -->
            <div class="p-6 border-t border-slate-700 space-y-3 flex-shrink-0">
                <button @click="retryConvert"
                        x-show="status === 'error'"
                        class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-xl font-bold transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Retry
                </button>
                <button @click="downloadResult"
                        x-show="status === 'done'"
                        x-transition
                        class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-purple-600/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download B&W Image
                </button>
                <a href="{{ route('dashboard') }}"
                   class="w-full flex items-center justify-center px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-bold transition-all">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
    function bwEditor() {
        return {
            imageId: '{{ $image->id }}',
            status: 'processing', // processing | done | error
            previewUrl: '{{ asset("storage/" . $image->original_path) }}',
            errorMsg: '',

            init() {
                this.convert();
            },

            convert() {
                this.status = 'processing';
                this.errorMsg = '';
                axios.post('/api/v1/blackwhite', {
                    image_id: this.imageId,
                    intensity: 100,
                    brightness: 0,
                    contrast: 0,
                    sharpen: false,
                }).then(res => {
                    // ApiResponse wraps data inside res.data.data
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        this.previewUrl = url + '?t=' + Date.now();
                        this.status = 'done';
                    } else {
                        throw new Error('No result URL returned.');
                    }
                }).catch(err => {
                    this.status = 'error';
                    this.errorMsg = err.response?.data?.message || err.message || 'Unknown error.';
                });
            },

            retryConvert() {
                this.convert();
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
        }
    }
    </script>
</x-app-layout>
