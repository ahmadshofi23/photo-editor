@push('head-scripts')
    @vite('resources/js/dashboard.js')
@endpush

<x-app-layout>
    <div x-data="{ tab: window.location.hash.includes('history') ? 'history' : 'overview' }">
        
        <!-- Header & Tabs -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <div class="flex bg-slate-800 p-1 rounded-xl border border-slate-700 w-full sm:w-auto">
                <button @click="tab = 'overview'; window.location.hash = 'overview'" 
                        class="flex-1 sm:px-6 py-2 rounded-lg text-sm font-medium transition-all"
                        :class="tab === 'overview' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'">
                    Overview
                </button>
                <button @click="tab = 'history'; window.location.hash = 'history'" 
                        class="flex-1 sm:px-6 py-2 rounded-lg text-sm font-medium transition-all"
                        :class="tab === 'history' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white'">
                    History
                </button>
            </div>
        </div>

        <!-- Tab 1: Overview -->
        <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            
            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Uploads</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_upload'] }}</p>
                    </div>
                </div>
                
                <!-- Stat Card 2 -->
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Storage Used</p>
                        <p class="text-2xl font-bold text-white">{{ round($stats['storage_used'] / 1048576, 2) }} MB</p>
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 text-green-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Processed</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_processed'] }}</p>
                    </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Downloads</p>
                        <p class="text-2xl font-bold text-white">{{ DB::table('downloads')->whereIn('image_id', $images->pluck('id'))->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div id="upload" class="bg-slate-800 rounded-2xl border border-slate-700 p-8" x-data="uploadZone()">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-white">Quick Upload & Edit</h2>
                </div>
                
                <div @dragover.prevent="dragover = true" @dragleave.prevent="dragover = false" @drop.prevent="handleDrop"
                     class="border-2 border-dashed rounded-xl p-10 text-center transition-all cursor-pointer"
                     :class="dragover ? 'border-purple-500 bg-purple-500/10' : 'border-slate-600 bg-slate-900/50 hover:border-slate-500'">
                    <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden" accept="image/*">
                    <div @click="$refs.fileInput.click()">
                        <!-- Initial State -->
                        <div x-show="!uploadedImage && !uploading">
                            <div class="w-16 h-16 mx-auto bg-slate-800 rounded-full flex items-center justify-center mb-4 text-slate-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <p class="text-white font-medium mb-1">Click to upload or drag and drop</p>
                            <p class="text-sm text-slate-400">SVG, PNG, JPG or GIF (max. 10MB)</p>
                        </div>
                        
                        <!-- Success State -->
                        <div x-show="uploadedImage && !uploading" style="display: none;" class="flex flex-col items-center">
                            <div class="relative w-24 h-24 mb-4 rounded-xl overflow-hidden border-2 border-green-500 shadow-lg shadow-green-500/20">
                                <img :src="uploadedFileUrl" alt="Uploaded Preview" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-green-500/20 flex items-center justify-center">
                                    <div class="bg-green-500 rounded-full p-1 text-white shadow">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <p class="text-green-400 font-bold mb-1">Image Uploaded Successfully!</p>
                            <p class="text-sm text-slate-400" x-text="uploadedFileName"></p>
                            <p class="text-xs text-purple-400 mt-2 hover:text-purple-300">Click to upload a different image</p>
                        </div>
                    </div>

                    <!-- Uploading state -->
                    <div x-show="uploading" style="display: none;" class="mt-4">
                        <div class="w-full bg-slate-700 rounded-full h-2.5">
                            <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="text-sm text-slate-400 mt-2">Uploading... <span x-text="progress + '%'"></span></p>
                    </div>
                </div>
                
                <!-- Quick Tools (shown when an image is selected) -->
                <div x-show="uploadedImage" class="mt-6 flex flex-wrap gap-4" x-transition>
                    <a :href="`/editor/resize/${uploadedImage}`" class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-slate-600">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Resize
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white">Recent Activity</h2>
                    <button @click="tab = 'history'" class="text-sm text-purple-400 hover:text-purple-300">View All</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700">
                                <th class="p-4 font-semibold">Image</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Date</th>
                                <th class="p-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-300">
                            @forelse($images->take(5) as $image)
                            <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded bg-slate-700 overflow-hidden">
                                            <img src="{{ asset('storage/'.$image->original_path) }}" class="w-full h-full object-cover">
                                        </div>
                                        <span class="font-medium text-white truncate max-w-[150px]">{{ basename($image->original_path) }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($image->edited_path)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Processed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Original
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-slate-400">{{ $image->created_at->diffForHumans() }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/editor/resize/{{ $image->id }}" title="Resize" class="w-8 h-8 rounded-full bg-slate-700 text-white flex items-center justify-center hover:bg-slate-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500">No images uploaded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Tab 2: History -->
        <div x-show="tab === 'history'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($images as $image)
                <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden flex flex-col group">
                    <!-- Image Preview -->
                    <div class="relative h-48 bg-slate-900 border-b border-slate-700 overflow-hidden">
                        @if($image->edited_path)
                            <img src="{{ asset('storage/'.$image->edited_path) }}" class="w-full h-full object-contain">
                            <div class="absolute top-2 right-2 bg-slate-900/80 backdrop-blur text-xs px-2 py-1 rounded text-slate-300 border border-slate-700">Edited</div>
                        @else
                            <img src="{{ asset('storage/'.$image->original_path) }}" class="w-full h-full object-contain">
                            <div class="absolute top-2 right-2 bg-slate-900/80 backdrop-blur text-xs px-2 py-1 rounded text-slate-300 border border-slate-700">Original</div>
                        @endif
                        
                        <!-- Hover Overlay Actions -->
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <a href="/editor/resize/{{ $image->id }}" title="Resize" class="w-8 h-8 rounded-full bg-green-600/80 text-white flex items-center justify-center hover:bg-green-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            </a>
                            <button @click="downloadImage({{ $image->id }}, '{{ $image->edited_path ? 'edited' : 'original' }}')" title="Download" class="w-8 h-8 rounded-full bg-slate-700/80 text-white flex items-center justify-center hover:bg-slate-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </button>
                            <button @click="deleteImage({{ $image->id }})" title="Delete" class="w-8 h-8 rounded-full bg-red-600/80 text-white flex items-center justify-center hover:bg-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Details -->
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-sm font-medium text-white truncate pr-4" title="{{ basename($image->original_path) }}">{{ basename($image->original_path) }}</h3>
                            <span class="text-xs text-slate-500 whitespace-nowrap">{{ round($image->size / 1024, 1) }} KB</span>
                        </div>
                        <p class="text-xs text-slate-400 mb-4">{{ $image->created_at->format('M d, Y') }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-700/50">
                            <div class="flex gap-2">
                                @foreach($image->histories->take(3) as $history)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-700 text-slate-300">
                                        {{ str_replace('_', ' ', $history->action_type) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($images->isEmpty())
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-slate-900 rounded-full flex items-center justify-center mb-4 border border-slate-700 text-slate-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-white mb-2">No history yet</h3>
                    <p class="text-slate-400 mb-6">Upload an image to start editing and building your history.</p>
                    <button @click="tab = 'overview'" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Go to Upload
                    </button>
                </div>
            @endif

        </div>

    </div>

</x-app-layout>
