<x-app-layout>
    <div x-data="resizeEditor()" class="h-[calc(100vh-10rem)] flex flex-col lg:flex-row gap-6">

        <!-- Left Panel: Image Preview -->
        <div class="w-full lg:w-2/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col overflow-hidden">

            <!-- Top bar -->
            <div class="h-14 border-b border-slate-700 flex items-center justify-between px-6 bg-slate-800/50 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <h2 class="text-white font-semibold">Smart Resize</h2>
                </div>
                <div x-show="resultDim" class="text-xs text-blue-400 font-mono bg-blue-500/10 px-2 py-1 rounded" x-text="resultDim"></div>
            </div>

            <!-- Canvas -->
            <div class="flex-1 relative bg-slate-900/50 overflow-hidden">
                <div class="absolute inset-0">
                    <!-- Original / Result image -->
                    <img :src="originalUrl"
                         x-ref="image"
                         class="absolute inset-0 w-full h-full object-contain"
                         alt="Original"
                         @load="initCropper"
                         x-show="status !== 'done'">
                         
                    <img :src="previewUrl"
                         class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                         alt="Preview"
                         x-show="status === 'done'">

                    <!-- Processing overlay -->
                    <div x-show="status === 'processing'" class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm flex flex-col items-center justify-center z-20 gap-4">
                        <svg class="w-12 h-12 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-white text-sm font-medium" x-text="`Resizing to ${activePresetLabel}…`"></p>
                    </div>

                    <!-- Idle hint -->
                    <div x-show="status === 'idle'" class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                        <div class="text-center bg-slate-900/80 backdrop-blur px-4 py-2 rounded-xl border border-slate-700">
                            <svg class="w-6 h-6 text-slate-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            <p class="text-slate-300 text-xs font-medium">Select a size to start cropping</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full lg:w-1/3 h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col">
            <div class="p-6 flex-1 overflow-y-auto space-y-6">

                <div>
                    <h3 class="text-lg font-bold text-white mb-1">Resize Image</h3>
                    <p class="text-sm text-slate-400">Set the target size, adjust the crop box, then process.</p>
                </div>

                <!-- Custom Size -->
                <div class="space-y-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Custom Size (cm)</p>
                    <div class="flex gap-3">
                        <div class="flex-1 relative">
                            <label class="text-xs text-slate-500 absolute -top-2 left-3 bg-slate-800 px-1">Width (cm)</label>
                            <input type="number" step="0.1" x-model.number="customW" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none" placeholder="cm">
                        </div>
                        <div class="flex items-center text-slate-500">×</div>
                        <div class="flex-1 relative">
                            <label class="text-xs text-slate-500 absolute -top-2 left-3 bg-slate-800 px-1">Height (cm)</label>
                            <input type="number" step="0.1" x-model.number="customH" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none" placeholder="cm">
                        </div>
                    </div>
                    <button @click="setCustomSize"
                            class="w-full py-2.5 rounded-xl border transition-all flex items-center justify-center gap-2"
                            :class="activePreset === 'custom' ? 'border-blue-500 bg-blue-500/10 text-blue-300 font-medium' : 'border-slate-600 bg-slate-700 text-white hover:bg-slate-600 font-medium'">
                        Set Custom Size
                    </button>

                    <!-- B&W Button (below custom size) -->
                    <div class="border-t border-slate-700 pt-3 space-y-2">
                        <button @click="processBW"
                                :disabled="bwStatus === 'processing' || bwStatus === 'done'"
                                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl font-semibold text-sm transition-all"
                                :class="bwStatus === 'done'
                                    ? 'bg-emerald-600/20 border border-emerald-500/40 text-emerald-300 cursor-default'
                                    : 'bg-slate-900/50 border border-slate-600 text-slate-300 hover:border-purple-500 hover:text-purple-300 hover:bg-purple-500/5 disabled:opacity-50'">
                            <template x-if="bwStatus === 'processing'">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <template x-if="bwStatus !== 'processing'">
                                <span>&#9899;</span>
                            </template>
                            <span x-text="bwStatus === 'processing' ? 'Converting...' : bwStatus === 'done' ? '✓ Black & White Applied' : 'Convert to Black & White'"></span>
                        </button>
                        <p x-show="bwStatus === 'error'" class="text-xs text-red-400 text-center">Conversion failed. Try again.</p>
                    </div>
                </div>

                <hr class="border-slate-700">

                <!-- Presets -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Presets</p>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="preset in presets" :key="preset.key">
                            <button @click="setPreset(preset)"
                                    class="flex items-center justify-between px-4 py-3 rounded-xl border transition-all"
                                    :class="activePreset === preset.key
                                        ? 'border-blue-500 bg-blue-500/10 text-blue-300'
                                        : 'border-slate-700 bg-slate-900/50 text-white hover:border-slate-500 hover:bg-slate-700'">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg" x-text="preset.icon"></span>
                                    <div class="text-left">
                                        <p class="text-sm font-medium" x-text="preset.label"></p>
                                        <p class="text-xs text-slate-400" x-text="`${preset.w} × ${preset.h} px`"></p>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Resize error -->
                <div x-show="status === 'error'" class="rounded-xl border border-red-500/40 bg-red-500/5 p-4">
                    <p class="text-sm text-red-300 font-medium">Resize failed</p>
                    <p class="text-xs text-red-400 mt-1" x-text="errorMsg"></p>
                </div>

                <!-- Image info -->
                <div class="rounded-xl bg-slate-900/50 border border-slate-700 p-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Image Info</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Original</span>
                        <span class="text-white font-mono">{{ $image->width ?? '?' }} × {{ $image->height ?? '?' }} px</span>
                    </div>
                    <div x-show="resultDim" class="flex justify-between text-sm">
                        <span class="text-slate-400">Resized to</span>
                        <span class="text-blue-400 font-mono font-bold" x-text="resultDim"></span>
                    </div>
                </div>

            </div>

            <!-- Actions -->
            <div class="p-6 border-t border-slate-700 space-y-3 flex-shrink-0">
                <button @click="processResize"
                        x-show="status !== 'done'"
                        :disabled="!targetW || !targetH || status === 'processing'"
                        class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20">
                    <template x-if="status === 'processing'">
                        <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </template>
                    <template x-if="status !== 'processing'">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </template>
                    <span x-text="status === 'processing' ? 'Processing...' : 'Process & Crop Image'"></span>
                </button>
                <button @click="downloadResult"
                        x-show="status === 'done'"
                        x-transition
                        class="w-full flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-600/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Resized Image
                </button>
                <button @click="downloadBW"
                        x-show="bwStatus === 'done'"
                        x-transition
                        class="w-full flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-600/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download B&W Image
                </button>
                <a :href="`{{ route('editor.print') }}?image_id={{ $image->id }}`"
                   x-show="status === 'done' || bwStatus === 'done'"
                   x-transition
                   class="w-full flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-600/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print This Photo
                </a>
                <button @click="resetEditor"
                        x-show="status === 'done' || bwStatus === 'done'"
                        class="w-full flex items-center justify-center px-4 py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl font-bold transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    Undo All Changes
                </button>
                <a href="{{ route('dashboard') }}"
                   x-show="status !== 'done' && bwStatus !== 'done'"
                   class="w-full flex items-center justify-center px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-bold transition-all">
                    Back to Dashboard
                </a>
        </div>
    </div>

    <script>
    function resizeEditor() {
        return {
            imageId: '{{ $image->id }}',
            originalUrl: '{{ asset("storage/" . ($image->edited_path ?? $image->original_path)) }}',
            previewUrl: '{{ asset("storage/" . ($image->edited_path ?? $image->original_path)) }}',
            status: 'idle', // idle | ready | processing | done | error
            activePreset: '',
            activePresetLabel: '',
            resultDim: '',
            errorMsg: '',
            activeTab: 'resize', // 'resize' | 'bw'
            bwStatus: 'idle',    // idle | processing | done | error
            bwUrl: null,

            customW: null,
            customH: null,
            targetW: null,
            targetH: null,
            cropper: null,

            presets: [
                { key: 'photo_2x3',        label: 'Pas Foto 2x3',      icon: '📸', w: 236,  h: 354  },
                { key: 'photo_3x4',        label: 'Pas Foto 3x4',      icon: '📸', w: 354,  h: 472  },
                { key: 'photo_4x6',        label: 'Pas Foto 4x6',      icon: '📸', w: 472,  h: 709  },
                { key: 'instagram_post',   label: 'Instagram Post',    icon: '📱', w: 1080, h: 1080 },
                { key: 'instagram_story',  label: 'Instagram Story',   icon: '📱', w: 1080, h: 1920 },
                { key: 'facebook_cover',   label: 'Facebook Cover',    icon: '🌐', w: 820,  h: 312  },
                { key: 'youtube_thumb',    label: 'YouTube Thumbnail', icon: '▶️', w: 1280, h: 720  },
            ],

            initCropper() {
                // If cropper already exists, do not destroy it on @load. 
                // We handle image updates via this.cropper.replace() in processBW.
                if (this.cropper) {
                    return;
                }
                this.cropper = new Cropper(this.$refs.image, {
                    viewMode: 1,
                    dragMode: 'move',
                    aspectRatio: NaN,
                    autoCropArea: 0.9,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            },

            setPreset(preset) {
                this.activePreset = preset.key;
                this.activePresetLabel = preset.label;
                this.targetW = preset.w;
                this.targetH = preset.h;
                this.status = 'ready';
                if (this.cropper) {
                    this.cropper.setAspectRatio(preset.w / preset.h);
                }
            },

            setCustomSize() {
                if (!this.customW || !this.customH) return;
                this.activePreset = 'custom';
                this.activePresetLabel = `${this.customW}x${this.customH} cm`;
                
                // Convert cm to px assuming 300 DPI (1 cm ≈ 118.11 px)
                this.targetW = Math.round(this.customW * 118.11);
                this.targetH = Math.round(this.customH * 118.11);
                
                this.status = 'ready';
                if (this.cropper) {
                    this.cropper.setAspectRatio(this.customW / this.customH);
                }
            },

            processResize() {
                if (this.status === 'processing' || !this.targetW || !this.targetH) return;
                
                let cropData = {};
                if (this.cropper) {
                    cropData = this.cropper.getData(true); // gets rounded integers
                }

                let finalW = this.targetW;
                let finalH = this.targetH;

                this.status = 'processing';
                this.errorMsg = '';
                this.resultDim = '';

                axios.post('/api/v1/resize', {
                    image_id: this.imageId,
                    width: finalW,
                    height: finalH,
                    mode: 'cover',
                    maintainRatio: false,
                    preset: this.activePreset === 'custom' ? null : this.activePreset,
                    quality: 96,
                    crop_x: cropData.x,
                    crop_y: cropData.y,
                    crop_width: cropData.width,
                    crop_height: cropData.height
                }).then(res => {
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        this.previewUrl = url + '?t=' + Date.now();
                        this.status = 'done';
                        if (payload?.image_id) {
                            this.resultDim = `${finalW} × ${finalH} px`;
                        }
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                        
                        // Un-hide from print library if it was previously hidden
                        let hiddenIds = JSON.parse(localStorage.getItem('hiddenPrintPhotos') || '[]');
                        hiddenIds = hiddenIds.filter(id => parseInt(id) !== parseInt(this.imageId));
                        localStorage.setItem('hiddenPrintPhotos', JSON.stringify(hiddenIds));
                        
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Resized successfully!', type: 'success' } }));
                    } else {
                        throw new Error('No result URL returned.');
                    }
                }).catch(err => {
                    this.status = 'error';
                    this.errorMsg = err.response?.data?.message || err.message || 'Unknown error.';
                });
            },

            resetEditor() {
                this.status = 'processing'; // Show loading while resetting
                axios.post(`/api/v1/images/${this.imageId}/reset`)
                    .then(res => {
                        this.status = 'idle';
                        this.activePreset = '';
                        this.activePresetLabel = '';
                        this.targetW = null;
                        this.targetH = null;
                        this.customW = null;
                        this.customH = null;
                        this.resultDim = '';
                        this.errorMsg = '';
                        this.bwStatus = 'idle';
                        this.bwUrl = null;
                        
                        // Force UI to use original image again
                        this.originalUrl = '{{ asset("storage/" . $image->original_path) }}';
                        
                        // Re-init cropper with original image
                        if (this.cropper) {
                            this.cropper.replace(this.originalUrl, false);
                        } else {
                            this.$nextTick(() => {
                                this.initCropper();
                            });
                        }
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'All changes undone. Reverted to original.', type: 'success' } }));
                    }).catch(err => {
                        console.error('Reset failed', err);
                        this.status = 'idle';
                    });
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

            // ─── Black & White ────────────────────────────────
            processBW() {
                if (this.bwStatus === 'processing' || this.bwStatus === 'done') return;
                this.bwStatus = 'processing';
                axios.post('/api/v1/blackwhite', {
                    image_id: this.imageId,
                })
                .then(res => {
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        this.bwUrl = url;
                        const newUrl = url + '?t=' + Date.now();
                        this.previewUrl = newUrl;
                        this.originalUrl = newUrl; // Update source URL
                        if (this.cropper) {
                            this.cropper.replace(newUrl, true); // Keep crop box intact while replacing
                        }
                        this.bwStatus = 'done';
                        
                        // Un-hide from print library if it was previously hidden
                        let hiddenIds = JSON.parse(localStorage.getItem('hiddenPrintPhotos') || '[]');
                        hiddenIds = hiddenIds.filter(id => parseInt(id) !== parseInt(this.imageId));
                        localStorage.setItem('hiddenPrintPhotos', JSON.stringify(hiddenIds));
                        
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Black & White applied!', type: 'success' } }));
                    } else {
                        throw new Error('No result URL returned.');
                    }
                })
                .catch(err => {
                    this.bwStatus = 'error';
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'B&W conversion failed.', type: 'error' } }));
                });
            },

            downloadBW() {
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
