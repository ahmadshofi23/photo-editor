<section id="demo" class="py-24 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            
            <div class="w-full lg:w-1/2">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">See the magic in real-time.</h2>
                <p class="text-slate-400 text-lg mb-8">
                    Our Black & White engine uses advanced luminance mapping to preserve detail while removing color. Slide to see the difference.
                </p>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center text-slate-300">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Preserves natural contrast
                    </li>
                    <li class="flex items-center text-slate-300">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Zero quality loss
                    </li>
                    <li class="flex items-center text-slate-300">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Lightning fast processing
                    </li>
                </ul>

            </div>

            <div class="w-full lg:w-1/2">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10" 
                     x-data="{ sliderPos: 50, isDragging: false }"
                     @mousemove="if(isDragging) { let rect = $el.getBoundingClientRect(); sliderPos = Math.max(0, Math.min(100, (($event.clientX - rect.left) / rect.width) * 100)); }"
                     @mouseup.window="isDragging = false"
                     @mouseleave="isDragging = false"
                     @touchmove="if(isDragging) { let rect = $el.getBoundingClientRect(); sliderPos = Math.max(0, Math.min(100, (($event.touches[0].clientX - rect.left) / rect.width) * 100)); }">
                    
                    <div class="relative h-[400px] w-full bg-slate-800" :style="`--slider-pos: ${sliderPos}%`">
                        <!-- Colored Image (Background) -->
                        <img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="absolute inset-0 w-full h-full object-cover pointer-events-none" alt="Colored">
                        
                        <!-- B&W Image (Foreground, clipped) -->
                        <div class="absolute inset-0 clip-path-slider pointer-events-none">
                            <img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80&sat=-100" class="absolute inset-0 w-full h-full object-cover" alt="B&W">
                        </div>

                        <!-- Slider Handle -->
                        <div class="absolute top-0 bottom-0 w-1 bg-white cursor-ew-resize flex items-center justify-center -ml-[2px]"
                             :style="`left: ${sliderPos}%`"
                             @mousedown="isDragging = true"
                             @touchstart="isDragging = true">
                            <div class="w-8 h-8 bg-white rounded-full shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l-4 4 4 4m8-8l4 4-4 4"></path></svg>
                            </div>
                        </div>
                        
                        <div class="absolute bottom-4 left-4 bg-black/50 backdrop-blur px-3 py-1 rounded text-xs font-bold tracking-wider uppercase text-white">B&W</div>
                        <div class="absolute bottom-4 right-4 bg-black/50 backdrop-blur px-3 py-1 rounded text-xs font-bold tracking-wider uppercase text-white">Original</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
