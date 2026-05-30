<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-xl font-bold text-white tracking-tight">Selamat datang kembali</h2>
        <p class="text-sm text-slate-500 mt-1">Masuk ke akun EditPro Anda</p>
    </div>

    <x-auth-session-status class="mb-4 text-sm text-emerald-400" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-medium text-slate-400 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-medium text-slate-400">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-violet-400 hover:text-violet-300 transition-colors">Lupa password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-white/20 bg-white/5 text-violet-600 focus:ring-violet-500 focus:ring-offset-0">
            <label for="remember_me" class="text-xs text-slate-400">Ingat saya</label>
        </div>

        <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors shadow-lg shadow-violet-600/20 mt-2">
            Masuk
        </button>

    </form>
</x-guest-layout>
