<x-app-layout>
<div x-data="removeBgEditor()" class="h-[calc(100vh-10rem)] flex flex-col lg:flex-row gap-6">

    {{-- LEFT PANEL: Image Preview --}}
    <div class="w-full lg:w-2/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <div class="h-14 border-b border-slate-700 flex items-center justify-between px-6 bg-slate-800/50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-white font-semibold">Remove &amp; Change Background</h2>
            </div>
            <div x-show="step === 'bg'" class="flex items-center gap-2">
                <span class="text-xs text-emerald-400 font-mono bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20">Background Removed ✓</span>
            </div>
        </div>

        {{-- Canvas --}}
        <div class="flex-1 relative overflow-hidden">

            {{-- Checkerboard bg (shows through transparent areas) --}}
            <div class="absolute inset-0 checkerboard"></div>

            {{-- Original image (shown before removal) --}}
            <img :src="originalUrl"
                 class="absolute inset-0 w-full h-full object-contain"
                 x-show="step === 'idle'"
                 alt="Original">

            {{-- Result (transparent or with new bg) --}}
            <img :src="resultUrl"
                 class="absolute inset-0 w-full h-full object-contain"
                 x-show="resultUrl && step !== 'idle'"
                 alt="Result">

            {{-- Processing overlay --}}
            <div x-show="loading" class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm flex flex-col items-center justify-center z-20 gap-4">
                <svg class="w-12 h-12 text-purple-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <p class="text-white text-sm font-medium" x-text="loadingText"></p>
            </div>

        </div>
    </div>

    {{-- RIGHT PANEL: Controls --}}
    <div class="w-full lg:w-1/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col">
        <div class="p-6 flex-1 overflow-y-auto space-y-6">

            <div>
                <h3 class="text-lg font-bold text-white mb-1">Remove &amp; Change Background</h3>
                <p class="text-sm text-slate-400">Hapus background menggunakan AI, lalu pilih background baru.</p>
            </div>

            {{-- Step 1: Remove Background --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-purple-600/30 text-purple-400 text-xs flex items-center justify-center font-bold">1</span>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hapus Background</p>
                </div>

                <div x-show="step === 'idle' || step === 'removing'"
                     class="rounded-xl border border-slate-700 bg-slate-900/40 p-4 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <p class="text-sm text-slate-300">Powered by <span class="text-purple-400 font-semibold">Remove.bg AI</span></p>
                    <p class="text-xs text-slate-500">Mendukung foto manusia, produk, hewan, dan objek umum</p>
                </div>

                <div x-show="step === 'bg' || step === 'done'"
                     class="flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-400">Background Berhasil Dihapus</p>
                        <p class="text-xs text-slate-400">Sekarang pilih background baru di bawah</p>
                    </div>
                </div>

                <div x-show="removeError" class="rounded-xl border border-red-500/40 bg-red-500/5 p-3">
                    <p class="text-sm text-red-400 font-medium">Gagal menghapus background</p>
                    <p class="text-xs text-red-400/70 mt-1" x-text="removeError"></p>
                </div>
            </div>

            {{-- Step 2: Choose Background (shown after removal) --}}
            <div x-show="step === 'bg' || step === 'done'" class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-blue-600/30 text-blue-400 text-xs flex items-center justify-center font-bold">2</span>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pilih Background Baru</p>
                </div>

                {{-- Tabs --}}
                <div class="flex bg-slate-900/50 p-1 rounded-xl border border-slate-700 gap-1">
                    <button @click="bgTab = 'transparent'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="bgTab === 'transparent' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Transparan
                    </button>
                    <button @click="bgTab = 'color'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="bgTab === 'color' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Warna
                    </button>
                    <button @click="bgTab = 'image'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="bgTab === 'image' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'">
                        Gambar
                    </button>
                </div>

                {{-- Tab: Transparent --}}
                <div x-show="bgTab === 'transparent'" class="rounded-xl border border-slate-700 bg-slate-900/40 p-4 text-center space-y-2">
                    <div class="checkerboard w-16 h-16 rounded-lg mx-auto border border-slate-600"></div>
                    <p class="text-sm text-white font-medium">Simpan sebagai PNG Transparan</p>
                    <p class="text-xs text-slate-400">Format PNG, siap untuk desain grafis</p>
                </div>

                {{-- Tab: Solid Color --}}
                <div x-show="bgTab === 'color'" class="space-y-3">
                    <div class="grid grid-cols-6 gap-2">
                        <template x-for="color in bgColors" :key="color.value">
                            <button @click="selectedBgColor = color.value"
                                    :title="color.label"
                                    class="w-full aspect-square rounded-lg border-2 transition-all"
                                    :style="`background-color: ${color.value}`"
                                    :class="selectedBgColor === color.value ? 'border-white scale-110 shadow-lg' : 'border-transparent hover:border-slate-500'">
                            </button>
                        </template>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-xs text-slate-400 flex-shrink-0">Custom:</label>
                        <input type="color" x-model="selectedBgColor"
                               class="flex-1 h-9 rounded-lg bg-slate-900/50 border border-slate-700 cursor-pointer px-1">
                        <span class="text-xs text-slate-400 font-mono" x-text="selectedBgColor"></span>
                    </div>
                    <div class="w-full h-12 rounded-xl border border-slate-600 flex items-center justify-center text-xs text-slate-500 font-medium"
                         :style="`background-color: ${selectedBgColor}`">
                        Preview
                    </div>
                </div>

                {{-- Tab: Custom Image --}}
                <div x-show="bgTab === 'image'" class="space-y-3">
                    <div @click="$refs.bgFileInput.click()"
                         class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all"
                         :class="bgImagePreview ? 'border-blue-500 bg-blue-500/5' : 'border-slate-600 hover:border-slate-500 bg-slate-900/40'">
                        <input type="file" x-ref="bgFileInput" @change="handleBgFileSelect" class="hidden" accept="image/*">
                        <template x-if="!bgImagePreview">
                            <div>
                                <svg class="w-8 h-8 mx-auto text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-sm text-slate-300">Klik untuk upload background</p>
                                <p class="text-xs text-slate-500 mt-1">JPG, PNG, WebP · maks 10MB</p>
                            </div>
                        </template>
                        <template x-if="bgImagePreview">
                            <div class="relative">
                                <img :src="bgImagePreview" class="w-full h-32 object-cover rounded-lg">
                                <div class="absolute inset-0 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                    <span class="text-xs text-white font-semibold bg-blue-600/80 px-2 py-1 rounded">Klik untuk ganti</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="bgError" class="rounded-xl border border-red-500/40 bg-red-500/5 p-3">
                    <p class="text-xs text-red-400" x-text="bgError"></p>
                </div>
            </div>

            {{-- Image Info --}}
            <div class="rounded-xl bg-slate-900/50 border border-slate-700 p-4 space-y-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Info Gambar</p>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Dimensi</span>
                    <span class="text-white font-mono">{{ $image->width ?? '?' }} × {{ $image->height ?? '?' }} px</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Format</span>
                    <span class="text-white font-mono uppercase">{{ $image->extension }}</span>
                </div>
            </div>

        </div>

        {{-- Action Buttons --}}
        <div class="p-6 border-t border-slate-700 space-y-3 flex-shrink-0">

            {{-- Step 1 button: Remove Background --}}
            <button x-show="step === 'idle'"
                    @click="removeBackground"
                    :disabled="loading"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white rounded-xl font-bold transition-all shadow-lg shadow-purple-600/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                Hapus Background (AI)
            </button>

            {{-- Step 2 buttons: Apply Background / Download Transparent --}}
            <template x-if="step === 'bg' || step === 'done'">
                <div class="space-y-3">
                    <button x-show="bgTab !== 'transparent'"
                            @click="applyBackground"
                            :disabled="loading || (bgTab === 'image' && !bgImageFile)"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Terapkan Background
                    </button>

                    <button @click="downloadResult"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-600/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Hasil
                    </button>

                    <a :href="`{{ route('editor.print') }}?image_id={{ $image->id }}`"
                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Foto Ini
                    </a>
                </div>
            </template>

            <a href="{{ route('dashboard') }}"
               x-show="step === 'idle'"
               class="w-full flex items-center justify-center px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-bold transition-all">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.checkerboard {
    background-image:
        linear-gradient(45deg, #374151 25%, transparent 25%),
        linear-gradient(-45deg, #374151 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #374151 75%),
        linear-gradient(-45deg, transparent 75%, #374151 75%);
    background-size: 16px 16px;
    background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
}
</style>

<script>
function removeBgEditor() {
    return {
        imageId:      '{{ $image->id }}',
        originalUrl:  '{{ asset("storage/" . ($image->edited_path ?? $image->original_path)) }}',
        resultUrl:    null,

        step:         'idle',   // idle | removing | bg | applying | done
        loading:      false,
        loadingText:  '',
        removeError:  null,
        bgError:      null,

        bgTab:            'transparent', // transparent | color | image
        selectedBgColor:  '#ffffff',
        bgImagePreview:   null,
        bgImageFile:      null,

        bgColors: [
            { label: 'White',      value: '#ffffff' },
            { label: 'Light Gray', value: '#f3f4f6' },
            { label: 'Silver',     value: '#d1d5db' },
            { label: 'Gray',       value: '#6b7280' },
            { label: 'Dark Gray',  value: '#374151' },
            { label: 'Black',      value: '#000000' },
            { label: 'Red',        value: '#ef4444' },
            { label: 'Orange',     value: '#f97316' },
            { label: 'Amber',      value: '#f59e0b' },
            { label: 'Green',      value: '#22c55e' },
            { label: 'Blue',       value: '#3b82f6' },
            { label: 'Purple',     value: '#a855f7' },
        ],

        removeBackground() {
            this.loading      = true;
            this.loadingText  = 'AI sedang memproses gambar Anda…';
            this.step         = 'removing';
            this.removeError  = null;

            axios.post('/api/v1/remove-background', { image_id: this.imageId })
                .then(res => {
                    const payload = res.data.data || res.data;
                    this.resultUrl = (payload.edited_url || payload.original_url) + '?t=' + Date.now();
                    this.step     = 'bg';
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Background berhasil dihapus!', type: 'success' }
                    }));
                })
                .catch(err => {
                    this.removeError = err.response?.data?.message || err.response?.data?.error || 'Gagal menghapus background.';
                    this.step = 'idle';
                })
                .finally(() => { this.loading = false; });
        },

        applyBackground() {
            if (this.bgTab === 'image' && !this.bgImageFile) {
                this.bgError = 'Silakan pilih gambar background terlebih dahulu.';
                return;
            }

            this.loading     = true;
            this.loadingText = 'Menerapkan background…';
            this.bgError     = null;
            this.step        = 'applying';

            const formData = new FormData();
            formData.append('image_id', this.imageId);
            formData.append('bg_type',  this.bgTab);

            if (this.bgTab === 'color') {
                formData.append('bg_color', this.selectedBgColor);
            } else if (this.bgTab === 'image' && this.bgImageFile) {
                formData.append('bg_image', this.bgImageFile);
            }

            axios.post('/api/v1/change-background', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then(res => {
                const payload = res.data.data || res.data;
                this.resultUrl = (payload.edited_url || payload.original_url) + '?t=' + Date.now();
                this.step = 'done';
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Background berhasil diterapkan!', type: 'success' }
                }));
            })
            .catch(err => {
                this.bgError = err.response?.data?.message || 'Gagal menerapkan background.';
                this.step = 'bg';
            })
            .finally(() => { this.loading = false; });
        },

        handleBgFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.bgImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => { this.bgImagePreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        downloadResult() {
            axios.get(`/api/v1/download/${this.imageId}?type=edited`)
                .then(res => {
                    const url = res.data?.data?.download_url || res.data?.download_url;
                    if (url) window.location.href = url;
                })
                .catch(() => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Download gagal. Coba lagi.', type: 'error' }
                    }));
                });
        },
    };
}
</script>
</x-app-layout>
