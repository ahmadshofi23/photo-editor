@push('head-scripts')
    @vite('resources/js/dashboard.js')
@endpush

<x-app-layout>
<div x-data="{ tab: window.location.hash.includes('history') ? 'history' : 'overview' }">

    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-lg font-semibold text-white tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
        <div class="flex bg-white/5 p-0.5 rounded-xl border border-white/8 w-full sm:w-auto">
            <button @click="tab = 'overview'; window.location.hash = 'overview'"
                    class="flex-1 sm:px-5 py-1.5 rounded-lg text-xs font-medium transition-all"
                    :class="tab === 'overview' ? 'bg-white/10 text-white shadow-sm' : 'text-slate-500 hover:text-slate-300'">
                Overview
            </button>
            <button @click="tab = 'history'; window.location.hash = 'history'"
                    class="flex-1 sm:px-5 py-1.5 rounded-lg text-xs font-medium transition-all"
                    :class="tab === 'history' ? 'bg-white/10 text-white shadow-sm' : 'text-slate-500 hover:text-slate-300'">
                History
            </button>
        </div>
    </div>

    <!-- ─── TAB: OVERVIEW ──────────────────────────────────────────── -->
    <div x-show="tab === 'overview'"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-6">

        <!-- Stats row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @php
            $stats_items = [
                ['label' => 'Total Upload',    'value' => $stats['total_upload'],                          'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',                                    'color' => 'violet'],
                ['label' => 'Storage Dipakai', 'value' => round($stats['storage_used']/1048576, 2).' MB',  'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'blue'],
                ['label' => 'Diproses',        'value' => $stats['total_processed'],                       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                  'color' => 'emerald'],
                ['label' => 'Diunduh',         'value' => $stats['total_downloads'],                       'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',                               'color' => 'sky'],
            ];
            @endphp
            @foreach($stats_items as $s)
            <div class="bg-[#0f0f17] border border-white/6 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                            @if($s['color']==='violet') bg-violet-500/10 text-violet-400
                            @elseif($s['color']==='blue') bg-blue-500/10 text-blue-400
                            @elseif($s['color']==='emerald') bg-emerald-500/10 text-emerald-400
                            @else bg-sky-500/10 text-sky-400 @endif">
                    <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">{{ $s['label'] }}</p>
                    <p class="text-lg font-bold text-white leading-tight">{{ $s['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Upload area -->
        <div id="upload" class="bg-[#0f0f17] border border-white/6 rounded-2xl p-6" x-data="uploadZone()">
            <h2 class="text-sm font-semibold text-white mb-4">Upload & Edit Foto</h2>

            <div @dragover.prevent="dragover = true" @dragleave.prevent="dragover = false" @drop.prevent="handleDrop"
                 class="border border-dashed rounded-xl p-8 text-center transition-all cursor-pointer"
                 :class="dragover ? 'border-violet-500/60 bg-violet-500/5' : 'border-white/10 hover:border-white/20'">
                <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden" accept="image/*">
                <div @click="$refs.fileInput.click()">

                    <div x-show="!uploadedImage && !uploading">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center mx-auto mb-3 text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 font-medium mb-1">Klik untuk upload atau drag & drop</p>
                        <p class="text-xs text-slate-600">PNG, JPG, GIF — maks 10 MB</p>
                    </div>

                    <div x-show="uploadedImage && !uploading" style="display:none;" class="flex flex-col items-center">
                        <div class="relative w-20 h-20 mb-3 rounded-xl overflow-hidden border border-emerald-500/40 shadow-lg shadow-emerald-500/10">
                            <img :src="uploadedFileUrl" alt="Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-emerald-500/20 flex items-center justify-center">
                                <div class="bg-emerald-500 rounded-full p-0.5 text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-emerald-400 font-medium mb-0.5">Berhasil diupload!</p>
                        <p class="text-xs text-slate-500" x-text="uploadedFileName"></p>
                        <p class="text-xs text-violet-400 mt-2">Klik untuk ganti gambar</p>
                    </div>
                </div>

                <div x-show="uploading" style="display:none;" class="mt-4">
                    <div class="w-full bg-white/5 rounded-full h-1.5">
                        <div class="bg-violet-600 h-1.5 rounded-full transition-all duration-300" :style="`width:${progress}%`"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Mengupload… <span x-text="progress+'%'"></span></p>
                </div>
            </div>

            <div x-show="uploadedImage" x-transition class="flex flex-wrap gap-2 mt-4">
                <a :href="`/editor/resize/${uploadedImage}`"
                   class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Resize & Edit
                </a>
                <a :href="`/editor/remove-background/${uploadedImage}`"
                   class="flex items-center gap-2 bg-violet-600/10 hover:bg-violet-600/20 border border-violet-500/20 text-violet-300 hover:text-violet-200 px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Hapus Background
                </a>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="bg-[#0f0f17] border border-white/6 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white">Aktivitas Terbaru</h2>
                <button @click="tab = 'history'" class="text-xs text-violet-400 hover:text-violet-300 transition-colors">Lihat semua</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-3 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Gambar</th>
                            <th class="px-6 py-3 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-[10px] font-semibold text-slate-600 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($images->take(5) as $image)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/5 overflow-hidden flex-shrink-0">
                                        <img src="{{ asset('storage/'.$image->original_path) }}" class="w-full h-full object-cover">
                                    </div>
                                    <span class="text-slate-300 truncate max-w-[140px] text-xs">{{ basename($image->original_path) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($image->edited_path)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-500/8 text-emerald-400 border border-emerald-500/15">
                                        <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Processed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-white/5 text-slate-500 border border-white/8">
                                        <span class="w-1 h-1 rounded-full bg-slate-600"></span> Original
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">{{ $image->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="/editor/resize/{{ $image->id }}" title="Edit"
                                       class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="/editor/remove-background/{{ $image->id }}" title="Remove BG"
                                       class="w-7 h-7 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 text-violet-400 hover:text-violet-300 flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-xs text-slate-600">Belum ada gambar yang diupload.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── TAB: HISTORY ───────────────────────────────────────────── -->
    <div x-show="tab === 'history'" style="display:none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">

        @if($images->isEmpty())
            <div class="bg-[#0f0f17] border border-white/6 rounded-2xl p-12 text-center">
                <div class="w-12 h-12 mx-auto bg-white/5 rounded-2xl flex items-center justify-center mb-4 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-white font-medium mb-1">Belum ada history</p>
                <p class="text-xs text-slate-500 mb-5">Upload dan edit foto untuk memulai.</p>
                <button @click="tab = 'overview'"
                        class="bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold px-5 py-2 rounded-xl transition-colors">
                    Upload Foto
                </button>
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($images as $image)
            <div class="bg-[#0f0f17] border border-white/6 rounded-2xl overflow-hidden flex flex-col group hover:border-white/12 transition-colors">
                <!-- Preview -->
                <div class="relative h-44 bg-black/30 overflow-hidden">
                    @if($image->edited_path)
                        <img src="{{ asset('storage/'.$image->edited_path) }}" class="w-full h-full object-contain">
                        <span class="absolute top-2 right-2 text-[10px] px-2 py-0.5 rounded-md bg-black/60 text-emerald-400 border border-emerald-500/20 backdrop-blur">Edited</span>
                    @else
                        <img src="{{ asset('storage/'.$image->original_path) }}" class="w-full h-full object-contain">
                        <span class="absolute top-2 right-2 text-[10px] px-2 py-0.5 rounded-md bg-black/60 text-slate-400 border border-white/10 backdrop-blur">Original</span>
                    @endif

                    <!-- Hover actions -->
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href="/editor/resize/{{ $image->id }}" title="Edit"
                           class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <a href="/editor/remove-background/{{ $image->id }}" title="Remove BG"
                           class="w-8 h-8 rounded-lg bg-violet-500/20 hover:bg-violet-500/40 text-violet-300 flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </a>
                        <button @click="downloadImage({{ $image->id }}, '{{ $image->edited_path ? 'edited' : 'original' }}')" title="Download"
                                class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </button>
                        <button @click="deleteImage({{ $image->id }})" title="Delete"
                                class="w-8 h-8 rounded-lg bg-red-500/15 hover:bg-red-500/30 text-red-400 flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Meta -->
                <div class="p-4 flex-1 flex flex-col">
                    <p class="text-xs font-medium text-white truncate mb-0.5" title="{{ basename($image->original_path) }}">
                        {{ basename($image->original_path) }}
                    </p>
                    <p class="text-[11px] text-slate-600 mb-3">{{ $image->created_at->format('d M Y') }} · {{ round($image->size/1024, 1) }} KB</p>

                    @if($image->histories->count())
                    <div class="flex flex-wrap gap-1 mt-auto">
                        @foreach($image->histories->take(3) as $history)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-white/5 text-slate-500 border border-white/8">
                                {{ str_replace('_', ' ', $history->action_type) }}
                            </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</x-app-layout>
