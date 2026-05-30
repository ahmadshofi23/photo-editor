<div x-data="toastComponent()"
     @notify.window="add($event.detail)"
     class="fixed top-5 right-5 z-[100] flex flex-col gap-2 pointer-events-none w-72">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-xl text-sm"
             :class="{
                 'bg-[#0f0f17] border-white/10 text-slate-300': toast.type === 'info',
                 'bg-[#0a1a0f] border-emerald-500/25 text-emerald-300': toast.type === 'success',
                 'bg-[#1a0a0a] border-red-500/25 text-red-300': toast.type === 'error',
             }">

            <!-- Icon -->
            <div class="flex-shrink-0 mt-0.5">
                <svg x-show="toast.type === 'success'" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg x-show="toast.type === 'info'" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <p class="flex-1 leading-snug" x-text="toast.message"></p>

            <button @click="remove(toast.id)" class="flex-shrink-0 text-current opacity-40 hover:opacity-70 transition-opacity mt-0.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
function toastComponent() {
    return {
        toasts: [],
        add(notice) {
            const id = Date.now();
            this.toasts.push({ id, message: notice.message, type: notice.type || 'info', visible: true });
            setTimeout(() => this.remove(id), notice.timeout || 3500);
        },
        remove(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) {
                t.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 250);
            }
        }
    }
}
</script>
