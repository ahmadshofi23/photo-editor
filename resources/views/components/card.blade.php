<div {{ $attributes->merge(['class' => 'bg-slate-800 rounded-2xl border border-slate-700 shadow-sm']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-slate-700">
            {{ $header }}
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-4 bg-slate-800/50 border-t border-slate-700 rounded-b-2xl">
            {{ $footer }}
        </div>
    @endif
</div>
