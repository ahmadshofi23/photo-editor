<x-app-layout>
    <div x-data="printEditor()" class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}"
                    class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-white">Multi-Photo Print</h1>
                    <p class="text-sm text-slate-400">Mix photos of different sizes on the same page</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span x-show="queue.length > 0" class="text-sm text-slate-400"
                    x-text="`${totalPhotos} photos · ${pages.length} page(s)`"></span>
                <button @click="openPrintWindow" :disabled="queue.length === 0 || !activePaper"
                    class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl font-semibold transition-all shadow-lg shadow-blue-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Now
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">

            <!-- LEFT PANEL -->
            <div class="w-full lg:w-80 xl:w-96 flex-shrink-0 space-y-4">

                <!-- 1. Photo Library -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded bg-blue-600/30 text-blue-400 text-xs flex items-center justify-center font-bold">1</span>
                            Photo Library
                        </h3>
                        <button @click="addAll" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">+
                            Add All</button>
                    </div>
                    <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1 custom-scroll">
                        <template x-for="img in photos" :key="img.historyId">
                            <div class="rounded-xl border transition-all"
                                :class="isInQueue(img.historyId) ? 'border-blue-500 bg-blue-500/10' : 'border-slate-700 bg-slate-900/40'">

                                <!-- Main row: thumbnail + name + actions -->
                                <div class="flex items-center gap-3 p-2.5 cursor-pointer" @click="toggleQueue(img)">
                                    <div
                                        class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden bg-slate-700 relative">
                                        <img :src="img.url" class="w-full h-full object-cover">
                                        <div x-show="isInQueue(img.historyId)"
                                            class="absolute inset-0 bg-blue-600/60 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium truncate"
                                            :class="isInQueue(img.historyId) ? 'text-blue-300' : 'text-white'"
                                            x-text="img.displayName || img.name"></p>
                                        <p class="text-xs text-slate-400"
                                            x-text="img.type + (img.widthCm ? ` · ${img.widthCm}×${img.heightCm}cm` : '')">
                                        </p>
                                    </div>
                                    <!-- Action buttons (stop click propagation) -->
                                    <div class="flex items-center gap-1 flex-shrink-0" @click.stop>
                                        <!-- Rename -->
                                        <button @click="startRename(img)"
                                            class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white flex items-center justify-center transition-colors"
                                            title="Rename">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <!-- Delete -->
                                        <button @click="deletePhoto(img.id)"
                                            class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/25 text-red-400 hover:text-red-300 flex items-center justify-center transition-colors"
                                            title="Delete photo">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Inline rename input (shown when editing) -->
                                <div x-show="renamingId === img.id" @click.stop class="px-2.5 pb-2.5">
                                    <div class="flex gap-2">
                                        <input type="text" :id="'rename-' + img.id" x-model="renameValue"
                                            @keydown.enter="commitRename(img)" @keydown.escape="cancelRename"
                                            class="flex-1 bg-slate-800 border border-blue-500 rounded-lg px-2 py-1.5 text-white text-xs focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Enter a display name…">
                                        <button @click="commitRename(img)"
                                            class="px-2 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded-lg font-semibold transition-colors">Save</button>
                                        <button @click="cancelRename"
                                            class="px-2 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs rounded-lg transition-colors">✕</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <p x-show="photos.length === 0" class="text-center py-6 text-slate-500 text-sm">No photos yet.
                            Upload and edit photos first.</p>
                    </div>
                </div>

                <!-- 2. Print Queue -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded bg-emerald-600/30 text-emerald-400 text-xs flex items-center justify-center font-bold">2</span>
                            Queue
                            <span x-show="queue.length > 0"
                                class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-mono"
                                x-text="queue.length + ' item(s)'"></span>
                        </h3>
                        <button @click="clearQueue" x-show="queue.length > 0"
                            class="text-xs text-red-400 hover:text-red-300 transition-colors">Clear</button>
                    </div>
                    <div x-show="queue.length === 0"
                        class="py-8 text-center text-slate-500 text-sm border-2 border-dashed border-slate-700 rounded-xl">
                        Click photos above to add</div>
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1 custom-scroll">
                        <template x-for="(entry, idx) in queue" :key="entry.photo.historyId">
                            <div class="bg-slate-900/50 border border-slate-700 rounded-xl p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <img :src="entry.photo.url" class="w-9 h-9 object-cover rounded-lg flex-shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-white truncate" x-text="entry.photo.name">
                                        </p>
                                        <p class="text-xs text-slate-500"
                                            x-text="entry.photo.widthCm ? `${entry.photo.widthCm}×${entry.photo.heightCm}cm` : 'Size unknown'">
                                        </p>
                                    </div>
                                    <button @click="removeFromQueue(idx)"
                                        class="w-6 h-6 rounded bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400">Qty:</span>
                                    <button @click="entry.quantity > 1 && entry.quantity--"
                                        class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-white text-sm flex items-center justify-center transition-colors">−</button>
                                    <input type="number" x-model.number="entry.quantity" min="1" max="100"
                                        class="flex-1 bg-slate-800 border border-slate-600 rounded px-2 py-1 text-white text-xs text-center font-bold focus:border-emerald-500 outline-none">
                                    <button @click="entry.quantity < 100 && entry.quantity++"
                                        class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-white text-sm flex items-center justify-center transition-colors">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Paper & Gap -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4 space-y-3">
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center gap-2">
                        <span
                            class="w-5 h-5 rounded bg-purple-600/30 text-purple-400 text-xs flex items-center justify-center font-bold">3</span>
                        Paper & Spacing
                    </h3>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="paper in papers" :key="paper.key">
                            <button @click="activePaper = paper"
                                class="p-2 rounded-xl border transition-all text-center"
                                :class="activePaper?.key === paper.key ? 'border-purple-500 bg-purple-500/10' : 'border-slate-700 bg-slate-900/40 hover:border-slate-500'">
                                <p class="text-sm font-semibold"
                                    :class="activePaper?.key === paper.key ? 'text-purple-300' : 'text-white'"
                                    x-text="paper.label"></p>
                                <p class="text-xs text-slate-400" x-text="`${paper.w}×${paper.h}cm`"></p>
                            </button>
                        </template>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Gap between photos</label>
                        <div class="flex gap-2">
                            <template x-for="g in [0,2,4,6]" :key="g">
                                <button @click="gap = g"
                                    class="flex-1 py-1.5 rounded-lg border text-xs font-medium transition-all"
                                    :class="gap===g ? 'border-purple-500 bg-purple-500/10 text-purple-300' : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white'"
                                    x-text="g===0?'None':g+'mm'"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Removed layout toggle to keep UI simple and predictable -->
                </div>

                <!-- Summary -->
                <div x-show="queue.length > 0 && activePaper"
                    class="bg-slate-900/60 rounded-2xl border border-slate-700 p-4 space-y-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Summary</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Total photos</span>
                        <span class="text-white font-bold" x-text="totalPhotos"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Paper</span>
                        <span class="text-white"
                            x-text="activePaper ? `${activePaper.label} (${activePaper.w}×${activePaper.h}cm)` : '-'"></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t border-slate-700 pt-2">
                        <span class="text-slate-300">Pages needed</span>
                        <span class="text-blue-400" x-text="pages.length + ' page(s)'"></span>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Preview -->
            <div class="flex-1 min-w-0">
                <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
                    <!-- Toolbar -->
                    <div class="h-12 border-b border-slate-700 flex items-center justify-between px-5">
                        <p class="text-sm font-semibold text-slate-300">Print Preview</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">Zoom:</span>
                            <button @click="previewScale = Math.max(0.2, +(previewScale-0.1).toFixed(1))"
                                class="w-6 h-6 rounded bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">−</button>
                            <span class="text-xs text-slate-400 font-mono w-10 text-center"
                                x-text="Math.round(previewScale*100)+'%'"></span>
                            <button @click="previewScale = Math.min(3, +(previewScale+0.1).toFixed(1))"
                                class="w-6 h-6 rounded bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">+</button>
                        </div>
                    </div>

                    <!-- Canvas -->
                    <div class="p-6 bg-slate-900/40 min-h-[600px] overflow-auto flex flex-col items-center gap-8">

                        <!-- Empty -->
                        <div x-show="queue.length === 0 || !activePaper"
                            class="flex flex-col items-center justify-center py-24 text-center w-full">
                            <div class="w-20 h-20 rounded-2xl bg-slate-700/40 flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-slate-400 font-medium">Add photos to the queue to see a preview</p>
                        </div>

                        <!-- Pages -->
                        <template x-if="queue.length > 0 && activePaper && pages.length > 0">
                            <div class="flex flex-col items-center gap-8 w-full">
                                <template x-for="(page, pi) in pages" :key="pi">
                                    <div class="flex flex-col items-center gap-1">
                                        <p class="text-xs text-slate-500 font-mono"
                                            x-text="`Page ${pi+1} of ${pages.length}`"></p>
                                        <!-- Paper -->
                                        <div class="bg-white shadow-2xl shadow-black/50 relative overflow-hidden flex-shrink-0"
                                            :style="paperStyle">
                                            <!-- Rows of photos -->
                                            <div class="absolute inset-0" :style="`padding:${padPx}px`">
                                                <div
                                                    :style="`display:flex;flex-wrap:wrap;gap:${gapPx}px;align-content:flex-start;`">
                                                    <template x-for="(photo, ci) in page" :key="ci">
                                                        <div :style="cellStyleFor(photo)"
                                                            class="overflow-hidden bg-gray-100 flex-shrink-0">
                                                            <img :src="photo.url"
                                                                style="width:100%;height:100%;object-fit:cover;display:block;">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-600"
                                            x-text="activePaper ? `${activePaper.w} × ${activePaper.h} cm` : ''"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
    </style>

    <script>
        function printEditor() {
            return {
                photos: @json($photos).map(p => ({ ...p, displayName: '' })),
                preSelectedId: @json($selectedId ?? null),
                // Queue is keyed by historyId (unique per crop version) not by image id
                queue: JSON.parse(localStorage.getItem('printQueueV2') || '[]'),
                activePaper: null,
                gap: 2,
                mixMode: 'grouped', // Default to grouped so images append sequentially
                previewScale: 0.5,
                CM: 37.795,         // 1cm = 37.795 screen px at 96dpi
                renamingId: null,
                renameValue: '',

                papers: [
                    { key: 'a4', label: 'A4', w: 21, h: 29.7 },
                    { key: 'a5', label: 'A5', w: 14.8, h: 21 },
                    { key: 'a6', label: 'A6', w: 10.5, h: 14.8 },
                    { key: 'f4', label: 'F4', w: 21.5, h: 33 },
                    { key: '4r', label: '4R(4×6")', w: 10.16, h: 15.24 },
                    { key: '5r', label: '5R(5×7")', w: 12.7, h: 17.78 },
                    { key: '3r', label: '3R(3.5×5")', w: 8.89, h: 12.7 },
                ],

                init() {
                    // Filter out hidden photo IDs from localStorage
                    let hiddenIds = JSON.parse(localStorage.getItem('hiddenPrintPhotos') || '[]');
                    this.photos = this.photos.filter(p => !hiddenIds.includes(p.id));

                    this.activePaper = this.papers[0];

                    if (this.preSelectedId) {
                        // Find the LATEST history entry for this image_id (last in the photos array
                        // because PrintController orders histories asc, so latest is last)
                        const candidates = this.photos.filter(p => p.id == this.preSelectedId);
                        const latest = candidates[candidates.length - 1];
                        if (latest && !this.isInQueue(latest.historyId)) {
                            this.addToQueue(latest);
                        }
                    }

                    // Watch queue and persist to localStorage (keyed by historyId)
                    this.$watch('queue', (val) => {
                        localStorage.setItem('printQueueV2', JSON.stringify(val));
                    }, { deep: true });
                },

                // ─── Queue (keyed by historyId = unique per crop version) ────
                isInQueue(hid) { return this.queue.some(e => e.photo.historyId === hid); },
                toggleQueue(img) { const i = this.queue.findIndex(e => e.photo.historyId === img.historyId); i >= 0 ? this.queue.splice(i, 1) : this.addToQueue(img); },
                addToQueue(img) { this.queue.push({ photo: img, quantity: 4 }); },
                removeFromQueue(idx) { this.queue.splice(idx, 1); },
                clearQueue() { this.queue = []; },
                addAll() { this.photos.forEach(p => { if (!this.isInQueue(p.historyId)) this.addToQueue(p); }); },

                // ─── Hide photo from library (Local only) ──────────────
                deletePhoto(id) {
                    if (!confirm('Remove this photo from the library view? (The original photo will NOT be deleted from your dashboard)')) return;
                    // Remove from queue if present (by ID because we hide all versions of this ID from library)
                    this.queue = this.queue.filter(e => e.photo.id !== id);
                    // Remove from library view locally
                    this.photos = this.photos.filter(p => p.id !== id);

                    // Save to localStorage so it persists across reloads
                    let hiddenIds = JSON.parse(localStorage.getItem('hiddenPrintPhotos') || '[]');
                    if (!hiddenIds.includes(id)) {
                        hiddenIds.push(id);
                        localStorage.setItem('hiddenPrintPhotos', JSON.stringify(hiddenIds));
                    }

                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Photo removed from print library.', type: 'success' } }));
                },

                // ─── Rename photo ─────────────────────────────────────
                startRename(img) {
                    this.renamingId = img.id;
                    this.renameValue = img.displayName || img.name;
                    this.$nextTick(() => {
                        const el = document.getElementById('rename-' + img.id);
                        if (el) { el.focus(); el.select(); }
                    });
                },
                commitRename(img) {
                    const trimmed = this.renameValue.trim();
                    if (trimmed) {
                        img.displayName = trimmed;
                        // Also update queue entries
                        this.queue.forEach(e => {
                            if (e.photo.id === img.id) e.photo.displayName = trimmed;
                        });
                    }
                    this.cancelRename();
                },
                cancelRename() { this.renamingId = null; this.renameValue = ''; },

                get totalPhotos() { return this.queue.reduce((s, e) => s + e.quantity, 0); },

                // ─── Flatten queue → ordered photo list ───────────────
                get flatItems() {
                    const items = [];
                    if (this.mixMode === 'mixed') {
                        // interleave: 1 of each photo until all quantities exhausted
                        const counts = this.queue.map(e => e.quantity);
                        let remaining = this.totalPhotos;
                        while (remaining > 0) {
                            this.queue.forEach((e, i) => {
                                if (counts[i] > 0) { items.push(e.photo); counts[i]--; remaining--; }
                            });
                        }
                    } else {
                        // grouped: all copies of photo 1, then photo 2, etc.
                        this.queue.forEach(e => {
                            for (let i = 0; i < e.quantity; i++) items.push(e.photo);
                        });
                    }
                    return items;
                },

                // ─── Pack flatItems into pages (Area/Height heuristic with Flex Wrap) ─────
                get pages() {
                    if (!this.activePaper || this.flatItems.length === 0) return [];

                    const gCm = this.gap / 10;
                    const pad = 0.3;
                    const maxW = this.activePaper.w - pad * 2;
                    const maxH = this.activePaper.h - pad * 2;

                    const pagesArr = [];
                    let curPage = [];

                    // Heuristik sederhana: perkirakan apakah foto muat di halaman ini
                    // Berhubung kita akan pakai flex-wrap, kita pantau posisi "cursor" (x, y)
                    let curX = 0;
                    let curY = 0;
                    let rowMaxH = 0;

                    this.flatItems.forEach(photo => {
                        const pW = photo.widthCm ?? 5;
                        const pH = photo.heightCm ?? 7;

                        // Cek apakah muat di baris ini
                        if (curX + pW > maxW + 0.01) {
                            // Pindah baris
                            curX = 0;
                            curY += rowMaxH + (curPage.length > 0 ? gCm : 0);
                            rowMaxH = 0;
                        }

                        // Cek apakah setelah pindah baris muat di halaman ini secara vertikal
                        if (curY + pH > maxH + 0.01 && curPage.length > 0) {
                            // Pindah halaman
                            pagesArr.push(curPage);
                            curPage = [];
                            curX = 0;
                            curY = 0;
                            rowMaxH = 0;
                        }

                        curPage.push(photo);
                        curX += pW + gCm;
                        rowMaxH = Math.max(rowMaxH, pH);
                    });

                    if (curPage.length > 0) pagesArr.push(curPage);
                    return pagesArr;
                },

                // ─── Styles (screen preview) ─────────────────────────
                get paperStyle() {
                    if (!this.activePaper) return '';
                    return `width:${this.activePaper.w * this.CM * this.previewScale}px;height:${this.activePaper.h * this.CM * this.previewScale}px;box-sizing:border-box;`;
                },
                get padPx() { return 0.3 * this.CM * this.previewScale; },
                get gapPx() { return (this.gap / 10) * this.CM * this.previewScale; },

                cellStyleFor(photo) {
                    const w = (photo.widthCm ?? 5) * this.CM * this.previewScale;
                    const h = (photo.heightCm ?? 7) * this.CM * this.previewScale;
                    return `width:${w}px;height:${h}px;`;
                },

                // ─── Print ───────────────────────────────────────────
                openPrintWindow() {
                    if (!this.queue.length || !this.activePaper) return;
                    const paper = this.activePaper;
                    const gMm = this.gap;
                    const padMm = 3;

                    let pagesHtml = '';
                    this.pages.forEach(page => {
                        let cellsHtml = '';
                        page.forEach(photo => {
                            const pW = photo.widthCm ?? 5;
                            const pH = photo.heightCm ?? 7;
                            cellsHtml += `<div style="width:${pW}cm;height:${pH}cm;overflow:hidden;flex-shrink:0;">
                        <img src="${photo.url}" style="width:100%;height:100%;object-fit:cover;display:block;">
                    </div>`;
                        });
                        // Gunakan flex-wrap untuk setiap halaman
                        pagesHtml += `<div class="page">
                    <div style="display:flex;flex-wrap:wrap;gap:${gMm}mm;padding:${padMm}mm;align-content:flex-start;">${cellsHtml}</div>
                </div>`;
                    });

                    const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Photos</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#fff;}
@page{size:${paper.w}cm ${paper.h}cm;margin:0;}
.page{width:${paper.w}cm;height:${paper.h}cm;overflow:hidden;page-break-after:always;}
.page:last-child{page-break-after:avoid;}
</style></head><body>${pagesHtml}</body></html>`;

                    const win = window.open('', '_blank', 'width=900,height=750,scrollbars=yes');
                    if (!win) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Pop-up blocked. Please allow pop-ups.', type: 'error' } }));
                        return;
                    }
                    win.document.write(html);
                    win.document.close();
                    win.focus();
                    win.onload = () => setTimeout(() => win.print(), 400);
                },
            }
        }
    </script>
</x-app-layout>