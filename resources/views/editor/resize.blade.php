<x-app-layout>
@push('head-scripts')
<style>
/* Thin scrollbar for right panel on desktop */
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #475569; }
/* Override main overflow when in editor so page doesn't double-scroll */
main { overflow: hidden !important; }
</style>
@endpush
<div x-data="resizeEditor()" class="flex flex-col lg:flex-row gap-4 lg:gap-6 lg:h-[calc(100vh-9rem)]">

    <!-- ═══════════════════════════════════════════════════════
         LEFT: Canvas Preview
    ════════════════════════════════════════════════════════ -->
    <div class="w-full lg:w-3/5 lg:h-full bg-slate-800 rounded-2xl border border-slate-700 flex flex-col overflow-hidden" style="min-height: 420px;">

        <!-- Top bar -->
        <div class="h-14 border-b border-slate-700 flex items-center justify-between px-5 flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-white font-bold text-base">✂️ Photo Editor</h2>
            </div>

            <!-- Progress pills -->
            <div class="hidden sm:flex items-center gap-2 text-xs">
                <template x-for="(step, i) in steps" :key="i">
                    <div class="flex items-center gap-1">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-xs transition-all"
                             :class="step.done ? 'bg-emerald-500 text-white' : (activeStep === i ? 'bg-blue-500 text-white' : 'bg-slate-700 text-slate-400')"
                             x-text="step.done ? '✓' : (i+1)"></div>
                        <span class="text-slate-400" x-text="step.label"></span>
                        <span x-show="i < steps.length - 1" class="text-slate-600 mx-1">›</span>
                    </div>
                </template>
            </div>

            <div x-show="resultDim" class="text-xs text-blue-400 font-mono bg-blue-500/10 px-2 py-1 rounded-lg" x-text="resultDim"></div>
        </div>

        <!-- Canvas -->
        <div class="flex-1 relative overflow-hidden" style="min-height: 400px;">
            <!-- Checkerboard bg (shows transparency) -->
            <div class="absolute inset-0"
                 :style="showCheckerboard ? 'background-image: repeating-conic-gradient(#334155 0% 25%, #1e293b 0% 50%) ; background-size: 24px 24px;' : 'background: #0f172a'">
            </div>

            <img :src="originalUrl"
                 x-ref="image"
                 class="absolute inset-0 w-full h-full object-contain"
                 alt="Original"
                 @load="initCropper"
                 x-show="mainStatus !== 'done'">

            <img :src="previewUrl"
                 class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                 alt="Preview"
                 x-show="mainStatus === 'done'">

            <!-- Processing overlay -->
            <div x-show="mainStatus === 'processing'" x-transition
                 class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-white font-semibold" x-text="processingMsg"></p>
                    <p class="text-slate-400 text-sm mt-1">Mohon tunggu sebentar…</p>
                </div>
            </div>

            <!-- Idle hint — only when no crop selected -->
            <div x-show="mainStatus === 'idle' && !targetW"
                 class="absolute bottom-4 left-1/2 -translate-x-1/2 pointer-events-none z-10">
                <div class="text-center bg-slate-900/90 backdrop-blur px-4 py-3 rounded-xl border border-slate-700 shadow-xl">
                    <p class="text-slate-300 text-sm font-medium">👈 Mulai dari panel kanan</p>
                    <p class="text-slate-500 text-xs mt-1">Ikuti langkah 1, 2, 3 secara berurutan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         RIGHT: Step-by-step Controls
    ════════════════════════════════════════════════════════ -->
    <div class="w-full lg:w-2/5 lg:h-full lg:overflow-y-auto lg:pr-1 flex flex-col gap-4 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">

        <!-- ─── STEP 1: Background ─────────────────────── -->
        <div class="bg-slate-800 rounded-2xl border transition-all"
             :class="activeStep === 0 ? 'border-blue-500/60 shadow-lg shadow-blue-500/10' : 'border-slate-700'">

            <!-- Step header -->
            <button @click="activeStep = 0" class="w-full flex items-center justify-between p-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 transition-all"
                         :class="steps[0].done ? 'bg-emerald-500 text-white' : 'bg-blue-500 text-white'">
                        <span x-show="!steps[0].done">1</span>
                        <span x-show="steps[0].done">✓</span>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">Latar Belakang</p>
                        <p class="text-slate-400 text-xs">Hapus atau ganti warna latar foto</p>
                    </div>
                </div>
                <div x-show="steps[0].done" class="text-emerald-400 text-xs font-medium bg-emerald-500/10 px-2 py-1 rounded-full">Selesai</div>
                <div x-show="!steps[0].done" class="text-blue-400">
                    <svg class="w-4 h-4 transition-transform" :class="activeStep === 0 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>

            <div x-show="activeStep === 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="px-4 pb-4 space-y-4 border-t border-slate-700 pt-4">

                    <!-- Remove BG -->
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Langkah 1a — Hapus Latar</p>
                        <button @click="processRemoveBG"
                                :disabled="bgStatus === 'processing' || bgStatus === 'done'"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border font-semibold text-sm transition-all"
                                :class="bgStatus === 'done'
                                    ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300 cursor-default'
                                    : bgStatus === 'processing'
                                        ? 'border-blue-500/40 bg-blue-500/10 text-blue-300'
                                        : 'border-slate-600 bg-slate-700/50 text-white hover:border-purple-500 hover:bg-purple-500/10 hover:text-purple-300'">
                            <template x-if="bgStatus === 'processing'">
                                <svg class="w-5 h-5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <template x-if="bgStatus !== 'processing'">
                                <span class="text-lg flex-shrink-0" x-text="bgStatus === 'done' ? '✅' : '🪄'"></span>
                            </template>
                            <div class="text-left">
                                <p x-text="bgStatus === 'processing' ? 'Sedang menghapus latar...' : bgStatus === 'done' ? 'Latar berhasil dihapus!' : 'Hapus Latar Otomatis (AI)'"></p>
                                <p x-show="bgStatus === 'idle'" class="text-xs font-normal opacity-60">Foto akan menjadi transparan</p>
                            </div>
                        </button>
                        <p x-show="bgStatus === 'error'" class="text-xs text-red-400 mt-1 px-1">Gagal menghapus latar. Coba lagi.</p>
                    </div>

                    <!-- Change BG Color -->
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Langkah 1b — Ganti Warna Latar</p>
                        <p class="text-xs text-slate-500 mb-3">Pilih warna, lalu klik "Terapkan Warna Latar"</p>

                        <!-- Color swatches -->
                        <div class="grid grid-cols-8 gap-2 mb-3">
                            <template x-for="color in bgColorSwatches" :key="color">
                                <button @click="selectedBgColor = color"
                                        :title="color"
                                        class="w-8 h-8 rounded-lg border-2 transition-all hover:scale-110"
                                        :style="`background-color: ${color}`"
                                        :class="selectedBgColor === color ? 'border-blue-400 scale-110 shadow-lg' : 'border-transparent'">
                                </button>
                            </template>
                        </div>

                        <!-- Custom color picker -->
                        <div class="flex items-center gap-2 mb-3">
                            <label class="text-xs text-slate-400 flex-shrink-0">Warna kustom:</label>
                            <div class="flex items-center gap-2 flex-1 bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2">
                                <input type="color" x-model="selectedBgColor" class="w-7 h-7 rounded cursor-pointer border-0 bg-transparent p-0">
                                <span class="text-white font-mono text-sm flex-1" x-text="selectedBgColor"></span>
                            </div>
                        </div>

                        <!-- Preview swatch -->
                        <div class="flex items-center gap-3 mb-3 p-3 bg-slate-900/50 rounded-xl border border-slate-700">
                            <div class="w-10 h-10 rounded-lg border border-slate-600 flex-shrink-0"
                                 :style="`background-color: ${selectedBgColor}`"></div>
                            <div>
                                <p class="text-white text-sm font-medium">Warna dipilih</p>
                                <p class="text-slate-400 text-xs font-mono" x-text="selectedBgColor"></p>
                            </div>
                        </div>

                        <button @click="processChangeBG"
                                :disabled="changeBgStatus === 'processing'"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl border font-semibold text-sm transition-all"
                                :class="changeBgStatus === 'done'
                                    ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300'
                                    : changeBgStatus === 'processing'
                                        ? 'border-blue-500/40 bg-blue-500/10 text-blue-300'
                                        : 'border-slate-600 bg-slate-700/50 text-white hover:border-blue-500 hover:bg-blue-500/10 hover:text-blue-300'">
                            <template x-if="changeBgStatus === 'processing'">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <span x-text="changeBgStatus === 'processing' ? 'Menerapkan warna...' : changeBgStatus === 'done' ? '✓ Warna Latar Diterapkan' : '🎨 Terapkan Warna Latar'"></span>
                        </button>
                        <p x-show="changeBgStatus === 'error'" class="text-xs text-red-400 mt-1 px-1">Gagal mengganti warna. Coba hapus latar dulu.</p>
                    </div>

                    <!-- Skip / Next -->
                    <div class="flex gap-2 pt-2 border-t border-slate-700">
                        <button @click="skipStep(0)" class="flex-1 py-2 text-xs text-slate-400 hover:text-white rounded-lg hover:bg-slate-700 transition-all">
                            Lewati langkah ini →
                        </button>
                        <button x-show="bgStatus === 'done' || changeBgStatus === 'done'"
                                @click="activeStep = 1"
                                class="flex-1 py-2 text-xs font-semibold text-blue-300 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 transition-all">
                            Lanjut ke Langkah 2 →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── STEP 2: Resize ─────────────────────────── -->
        <div class="bg-slate-800 rounded-2xl border transition-all"
             :class="activeStep === 1 ? 'border-blue-500/60 shadow-lg shadow-blue-500/10' : 'border-slate-700'">

            <button @click="activeStep = 1" class="w-full flex items-center justify-between p-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 transition-all"
                         :class="steps[1].done ? 'bg-emerald-500 text-white' : (activeStep === 1 ? 'bg-blue-500 text-white' : 'bg-slate-700 text-slate-400')">
                        <span x-show="!steps[1].done">2</span>
                        <span x-show="steps[1].done">✓</span>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">Ukuran Foto</p>
                        <p class="text-slate-400 text-xs">Pilih ukuran atau masukkan ukuran sendiri</p>
                    </div>
                </div>
                <div x-show="steps[1].done" class="text-emerald-400 text-xs font-medium bg-emerald-500/10 px-2 py-1 rounded-full">Selesai</div>
                <div x-show="!steps[1].done">
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="activeStep === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>

            <div x-show="activeStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="px-4 pb-4 space-y-4 border-t border-slate-700 pt-4">

                    <!-- Preset grid — most prominent -->
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Pilih Ukuran Umum</p>
                        <div class="grid grid-cols-1 gap-2">
                            <template x-for="preset in presets" :key="preset.key">
                                <button @click="setPreset(preset)"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl border transition-all text-left"
                                        :class="activePreset === preset.key
                                            ? 'border-blue-500 bg-blue-500/10 text-blue-300'
                                            : 'border-slate-700 bg-slate-900/40 text-white hover:border-slate-500 hover:bg-slate-700/60'">
                                    <span class="text-xl flex-shrink-0" x-text="preset.icon"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm truncate" x-text="preset.label"></p>
                                        <p class="text-xs opacity-60" x-text="`${preset.w} × ${preset.h} piksel`"></p>
                                    </div>
                                    <div x-show="activePreset === preset.key" class="text-blue-400 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Custom size (collapsed by default) -->
                    <div>
                        <button @click="showCustomSize = !showCustomSize"
                                class="w-full flex items-center justify-between text-xs font-semibold text-slate-400 uppercase tracking-wider py-2 hover:text-white transition-colors">
                            <span>Atau Masukkan Ukuran Sendiri (cm)</span>
                            <svg class="w-4 h-4 transition-transform" :class="showCustomSize ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="showCustomSize" x-transition class="space-y-3 mt-2">
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="text-xs text-slate-400 mb-1 block">Lebar (cm)</label>
                                    <input type="number" step="0.1" x-model.number="customW"
                                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none transition-all"
                                           placeholder="cth: 3">
                                </div>
                                <div class="flex items-end pb-3 text-slate-500 font-bold">×</div>
                                <div class="flex-1">
                                    <label class="text-xs text-slate-400 mb-1 block">Tinggi (cm)</label>
                                    <input type="number" step="0.1" x-model.number="customH"
                                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none transition-all"
                                           placeholder="cth: 4">
                                </div>
                            </div>
                            <button @click="setCustomSize"
                                    class="w-full py-2.5 rounded-xl border font-semibold text-sm transition-all"
                                    :class="activePreset === 'custom' ? 'border-blue-500 bg-blue-500/10 text-blue-300' : 'border-slate-600 bg-slate-700 text-white hover:bg-slate-600'">
                                Gunakan Ukuran Ini
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div x-show="targetW" class="flex items-center gap-3 p-3 bg-blue-500/5 border border-blue-500/20 rounded-xl">
                        <span class="text-blue-400">ℹ️</span>
                        <div>
                            <p class="text-blue-300 text-xs font-medium">Ukuran dipilih: <span class="font-mono" x-text="`${targetW} × ${targetH} px`"></span></p>
                            <p class="text-slate-400 text-xs">Atur kotak seleksi di foto, lalu klik "Proses Foto"</p>
                        </div>
                    </div>

                    <!-- Next -->
                    <div class="flex gap-2 pt-2 border-t border-slate-700">
                        <button @click="skipStep(1)" class="flex-1 py-2 text-xs text-slate-400 hover:text-white rounded-lg hover:bg-slate-700 transition-all">
                            Lewati →
                        </button>
                        <button x-show="targetW" @click="activeStep = 2"
                                class="flex-1 py-2 text-xs font-semibold text-blue-300 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 transition-all">
                            Lanjut ke Langkah 3 →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── STEP 3: Style ──────────────────────────── -->
        <div class="bg-slate-800 rounded-2xl border transition-all"
             :class="activeStep === 2 ? 'border-blue-500/60 shadow-lg shadow-blue-500/10' : 'border-slate-700'">

            <button @click="activeStep = 2" class="w-full flex items-center justify-between p-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 transition-all"
                         :class="steps[2].done ? 'bg-emerald-500 text-white' : (activeStep === 2 ? 'bg-blue-500 text-white' : 'bg-slate-700 text-slate-400')">
                        <span x-show="!steps[2].done">3</span>
                        <span x-show="steps[2].done">✓</span>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">Gaya Foto (Opsional)</p>
                        <p class="text-slate-400 text-xs">Ubah ke hitam putih jika diperlukan</p>
                    </div>
                </div>
                <div x-show="steps[2].done" class="text-emerald-400 text-xs font-medium bg-emerald-500/10 px-2 py-1 rounded-full">Selesai</div>
                <div x-show="!steps[2].done">
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="activeStep === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>

            <div x-show="activeStep === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="px-4 pb-4 border-t border-slate-700 pt-4">
                    <button @click="processBW"
                            :disabled="bwStatus === 'processing' || bwStatus === 'done'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border font-semibold text-sm transition-all"
                            :class="bwStatus === 'done'
                                ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300 cursor-default'
                                : bwStatus === 'processing'
                                    ? 'border-blue-500/40 bg-blue-500/10 text-blue-300'
                                    : 'border-slate-600 bg-slate-700/50 text-white hover:border-purple-500 hover:bg-purple-500/10 hover:text-purple-300'">
                        <template x-if="bwStatus === 'processing'">
                            <svg class="w-5 h-5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <span x-show="bwStatus !== 'processing'" class="text-xl flex-shrink-0">⚫</span>
                        <div class="text-left">
                            <p x-text="bwStatus === 'processing' ? 'Mengubah ke hitam putih...' : bwStatus === 'done' ? '✓ Hitam Putih Diterapkan' : 'Ubah ke Hitam & Putih'"></p>
                            <p x-show="bwStatus === 'idle'" class="text-xs font-normal opacity-60">Cocok untuk foto formal / dokumen</p>
                        </div>
                    </button>
                    <p x-show="bwStatus === 'error'" class="text-xs text-red-400 mt-2 px-1">Konversi gagal. Coba lagi.</p>
                </div>
            </div>
        </div>

        <!-- ─── ACTION BUTTONS ────────────────────────── -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4 space-y-3">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Selesai & Unduh</p>

            <!-- Main process button -->
            <button @click="processResize"
                    x-show="mainStatus !== 'done'"
                    :disabled="!targetW || !targetH || mainStatus === 'processing'"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl font-bold text-sm transition-all shadow-lg"
                    :class="!targetW ? 'bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-600/20 disabled:opacity-60'">
                <template x-if="mainStatus === 'processing'">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </template>
                <span x-show="mainStatus !== 'processing'" class="text-base">✂️</span>
                <span x-text="!targetW ? 'Pilih ukuran dulu (Langkah 2)' : mainStatus === 'processing' ? 'Memproses foto...' : mainStatus === 'error' ? '⚠️ Gagal — Coba Lagi' : 'Proses & Potong Foto'"></span>
            </button>
            <p x-show="mainStatus === 'error' && errorMsg"
               class="text-xs text-red-400 mt-1 px-1 text-center"
               x-text="'❌ ' + errorMsg"></p>

            <!-- Download buttons (after done) -->
            <div x-show="mainStatus === 'done'" x-transition class="space-y-2">
                <div class="flex items-center gap-2 p-3 bg-emerald-500/5 border border-emerald-500/20 rounded-xl">
                    <span class="text-emerald-400 text-lg">🎉</span>
                    <div>
                        <p class="text-emerald-300 font-semibold text-sm">Foto berhasil diproses!</p>
                        <p class="text-slate-400 text-xs" x-text="resultDim ? `Ukuran: ${resultDim}` : 'Siap untuk diunduh'"></p>
                    </div>
                </div>
                <button @click="downloadResult"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-600/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Foto
                </button>
                <a :href="`{{ route('editor.print') }}?image_id={{ $image->id }}`"
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Foto Ini
                </a>
            </div>

            <!-- BW download -->
            <button @click="downloadBW"
                    x-show="bwStatus === 'done' && mainStatus !== 'done'"
                    x-transition
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Foto Hitam Putih
            </button>

            <!-- Undo / Back -->
            <div class="flex gap-2">
                <button @click="resetEditor"
                        x-show="mainStatus === 'done' || bwStatus === 'done' || bgStatus === 'done' || changeBgStatus === 'done'"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl font-semibold text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Batalkan Semua
                </button>
                <a href="{{ route('dashboard') }}"
                   x-show="mainStatus !== 'done' && bwStatus !== 'done'"
                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-semibold text-sm transition-all">
                    ← Dashboard
                </a>
            </div>
        </div>

        <!-- Image Info -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Info Foto</p>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Ukuran asli</span>
                    <span class="text-white font-mono">{{ $image->width ?? '?' }} × {{ $image->height ?? '?' }} px</span>
                </div>
                <div x-show="resultDim" class="flex justify-between text-sm">
                    <span class="text-slate-400">Setelah diproses</span>
                    <span class="text-blue-400 font-mono font-bold" x-text="resultDim"></span>
                </div>
            </div>
        </div>

    </div><!-- /right -->
</div>

<script>
function resizeEditor() {
    return {
        imageId: '{{ $image->id }}',
        originalUrl: '{{ asset("storage/" . ($image->edited_path ?? $image->original_path)) }}',
        previewUrl: '{{ asset("storage/" . ($image->edited_path ?? $image->original_path)) }}',

        // UI state
        activeStep: 0,
        showCustomSize: false,
        showCheckerboard: false,
        processingMsg: 'Memproses foto…',

        // Step tracking
        steps: [
            { label: 'Latar', done: false },
            { label: 'Ukuran', done: false },
            { label: 'Gaya', done: false },
        ],

        // Resize state
        mainStatus: 'idle',   // idle | ready | processing | done | error
        activePreset: '',
        activePresetLabel: '',
        resultDim: '',
        errorMsg: '',
        customW: null,
        customH: null,
        targetW: null,
        targetH: null,
        cropper: null,

        // B&W state
        bwStatus: 'idle',
        bwUrl: null,

        // Background state
        bgStatus: 'idle',         // idle | processing | done | error
        changeBgStatus: 'idle',   // idle | processing | done | error
        selectedBgColor: '#ffffff',

        bgColorSwatches: [
            '#ffffff', '#f8fafc', '#e2e8f0', '#94a3b8',
            '#0f172a', '#1e293b', '#ef4444', '#f97316',
            '#eab308', '#22c55e', '#3b82f6', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f59e0b',
        ],

        presets: [
            { key: 'photo_2x3',        label: 'Pas Foto 2×3',      icon: '📸', w: 236,  h: 354  },
            { key: 'photo_3x4',        label: 'Pas Foto 3×4',      icon: '📸', w: 354,  h: 472  },
            { key: 'photo_4x6',        label: 'Pas Foto 4×6',      icon: '📸', w: 472,  h: 709  },
            { key: 'instagram_post',   label: 'Instagram Post',    icon: '📱', w: 1080, h: 1080 },
            { key: 'instagram_story',  label: 'Instagram Story',   icon: '📱', w: 1080, h: 1920 },
            { key: 'facebook_cover',   label: 'Facebook Cover',    icon: '🌐', w: 820,  h: 312  },
            { key: 'youtube_thumb',    label: 'YouTube Thumbnail', icon: '▶️', w: 1280, h: 720  },
        ],

        initCropper() {
            if (this.cropper) return;
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

        skipStep(index) {
            this.steps[index].done = false;
            this.activeStep = Math.min(index + 1, 2);
        },

        setPreset(preset) {
            this.activePreset = preset.key;
            this.activePresetLabel = preset.label;
            this.targetW = preset.w;
            this.targetH = preset.h;
            this.mainStatus = 'ready';
            if (this.cropper) {
                this.cropper.setAspectRatio(preset.w / preset.h);
            }
        },

        setCustomSize() {
            if (!this.customW || !this.customH) return;
            this.activePreset = 'custom';
            this.activePresetLabel = `${this.customW}×${this.customH} cm`;
            this.targetW = Math.round(this.customW * 118.11);
            this.targetH = Math.round(this.customH * 118.11);
            this.mainStatus = 'ready';
            if (this.cropper) {
                this.cropper.setAspectRatio(this.customW / this.customH);
            }
        },

        processRemoveBG() {
            if (this.bgStatus === 'processing' || this.bgStatus === 'done') return;
            if (this.mainStatus === 'done') return; // don't allow BG ops after resize is done
            this.bgStatus = 'processing';
            this.processingMsg = 'AI sedang menghapus latar belakang…';
            this.mainStatus = 'processing';
            this.showCheckerboard = true;

            axios.post('/api/v1/remove-background', { image_id: this.imageId })
                .then(res => {
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        const freshUrl = url + '?t=' + Date.now();
                        this.previewUrl = freshUrl;
                        this.originalUrl = freshUrl;
                        if (this.cropper) {
                            this.cropper.replace(freshUrl, true);
                        }
                        this.bgStatus = 'done';
                        this.steps[0].done = true;
                        if (this.mainStatus !== 'done') this.mainStatus = 'idle';
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '✅ Latar berhasil dihapus!', type: 'success' } }));
                    } else {
                        throw new Error('No URL');
                    }
                })
                .catch(() => {
                    this.bgStatus = 'error';
                    if (this.mainStatus !== 'done') this.mainStatus = 'idle';
                    this.showCheckerboard = false;
                });
        },

        processChangeBG() {
            if (this.changeBgStatus === 'processing') return;
            if (this.mainStatus === 'done') return; // don't allow BG ops after resize is done
            this.changeBgStatus = 'processing';
            this.processingMsg = 'Mengganti warna latar…';
            this.mainStatus = 'processing';

            const formData = new FormData();
            formData.append('image_id', this.imageId);
            formData.append('bg_type', 'color');
            formData.append('bg_color', this.selectedBgColor);

            axios.post('/api/v1/change-background', formData)
                .then(res => {
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        const freshUrl = url + '?t=' + Date.now();
                        this.previewUrl = freshUrl;
                        this.originalUrl = freshUrl;
                        if (this.cropper) {
                            this.cropper.replace(freshUrl, true);
                        }
                        this.changeBgStatus = 'done';
                        this.steps[0].done = true;
                        if (this.mainStatus !== 'done') this.mainStatus = 'idle';
                        this.showCheckerboard = false;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '🎨 Warna latar berhasil diganti!', type: 'success' } }));
                    } else {
                        throw new Error('No URL');
                    }
                })
                .catch(() => {
                    this.changeBgStatus = 'error';
                    if (this.mainStatus !== 'done') this.mainStatus = 'idle';
                });
        },

        processResize() {
            if (this.mainStatus === 'processing' || !this.targetW || !this.targetH) return;

            let cropData = {};
            if (this.cropper) {
                cropData = this.cropper.getData(true);
            }

            this.mainStatus = 'processing';
            this.processingMsg = `Memotong & mengubah ukuran ke ${this.activePresetLabel}…`;
            this.errorMsg = '';
            this.resultDim = '';

            axios.post('/api/v1/resize', {
                image_id: this.imageId,
                width: this.targetW,
                height: this.targetH,
                mode: 'cover',
                maintainRatio: false,
                preset: this.activePreset === 'custom' ? null : this.activePreset,
                quality: 96,
                crop_x: cropData.x,
                crop_y: cropData.y,
                crop_width: cropData.width,
                crop_height: cropData.height,
            }).then(res => {
                const payload = res.data.data || res.data;
                const url = payload?.edited_url;
                if (url) {
                    this.previewUrl = url + '?t=' + Date.now();
                    this.mainStatus = 'done';
                    this.resultDim = `${this.targetW} × ${this.targetH} px`;
                    this.steps[1].done = true;
                    if (this.cropper) { this.cropper.destroy(); this.cropper = null; }

                    // Unhide any previously-hidden print slots for this image so the new version is visible
                    try {
                        let hiddenHistories = JSON.parse(localStorage.getItem('hiddenPrintHistories') || '[]');
                        hiddenHistories = hiddenHistories.filter(id => typeof id === 'string' && !id.startsWith('img_' + this.imageId + '_'));
                        localStorage.setItem('hiddenPrintHistories', JSON.stringify(hiddenHistories));
                    } catch (_) {}

                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '✅ Foto berhasil diproses!', type: 'success' } }));
                } else {
                    throw new Error('No result URL returned.');
                }
            }).catch(err => {
                this.mainStatus = 'error';
                this.errorMsg = err.response?.data?.message || err.message || 'Unknown error.';
            });
        },

        processBW() {
            if (this.bwStatus === 'processing' || this.bwStatus === 'done') return;
            this.bwStatus = 'processing';
            this.processingMsg = 'Mengubah foto ke hitam putih…';

            axios.post('/api/v1/blackwhite', { image_id: this.imageId })
                .then(res => {
                    const payload = res.data.data || res.data;
                    const url = payload?.edited_url;
                    if (url) {
                        const freshUrl = url + '?t=' + Date.now();
                        this.bwUrl = freshUrl;
                        this.previewUrl = freshUrl;
                        this.originalUrl = freshUrl;
                        if (this.cropper) {
                            this.cropper.replace(freshUrl, true);
                        }
                        this.bwStatus = 'done';
                        this.steps[2].done = true;

                        // Unhide any previously-hidden print slots for this image
                        try {
                            let hiddenHistories = JSON.parse(localStorage.getItem('hiddenPrintHistories') || '[]');
                            hiddenHistories = hiddenHistories.filter(id => typeof id === 'string' && !id.startsWith('img_' + this.imageId + '_'));
                            localStorage.setItem('hiddenPrintHistories', JSON.stringify(hiddenHistories));
                        } catch (_) {}

                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '⚫ Hitam putih diterapkan!', type: 'success' } }));
                    } else {
                        throw new Error('No result URL returned.');
                    }
                })
                .catch(() => {
                    this.bwStatus = 'error';
                });
        },

        resetEditor() {
            this.mainStatus = 'processing';
            this.processingMsg = 'Membatalkan semua perubahan…';

            axios.post(`/api/v1/images/${this.imageId}/reset`)
                .then(() => {
                    this.mainStatus = 'idle';
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
                    this.bgStatus = 'idle';
                    this.changeBgStatus = 'idle';
                    this.showCheckerboard = false;
                    this.steps = [{ label: 'Latar', done: false }, { label: 'Ukuran', done: false }, { label: 'Gaya', done: false }];
                    this.activeStep = 0;

                    this.originalUrl = '{{ asset("storage/" . $image->original_path) }}';

                    if (this.cropper) {
                        this.cropper.replace(this.originalUrl, false);
                    } else {
                        this.$nextTick(() => this.initCropper());
                    }
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Semua perubahan dibatalkan.', type: 'success' } }));
                })
                .catch(err => {
                    console.error('Reset failed', err);
                    this.mainStatus = 'idle';
                });
        },

        downloadResult() {
            axios.get(`/api/v1/download/${this.imageId}?type=edited`)
                .then(res => {
                    const url = res.data?.data?.download_url || res.data?.download_url;
                    if (url) window.location.href = url;
                }).catch(() => {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Gagal mengunduh. Coba lagi.', type: 'error' } }));
                });
        },

        downloadBW() {
            axios.get(`/api/v1/download/${this.imageId}?type=edited`)
                .then(res => {
                    const url = res.data?.data?.download_url || res.data?.download_url;
                    if (url) window.location.href = url;
                }).catch(() => {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Gagal mengunduh. Coba lagi.', type: 'error' } }));
                });
        },
    }
}
</script>
</x-app-layout>
