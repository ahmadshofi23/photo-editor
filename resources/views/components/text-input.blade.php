@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-700 bg-slate-900 text-slate-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm disabled:opacity-50']) }}>
