<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-xl font-bold text-white tracking-tight">Buat akun baru</h2>
        <p class="text-sm text-slate-500 mt-1">Mulai edit foto Anda secara gratis</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-medium text-slate-400 mb-1.5">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="Ahmad Shofi">
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-slate-400 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-slate-400 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="Min. 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-slate-400 mb-1.5">Konfirmasi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600
                          focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.07] transition-colors"
                   placeholder="••••••••">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors shadow-lg shadow-violet-600/20 mt-2">
            Daftar
        </button>

        <p class="text-center text-xs text-slate-500 pt-1">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-violet-400 hover:text-violet-300 transition-colors font-medium">Masuk</a>
        </p>
    </form>
</x-guest-layout>
