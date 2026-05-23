<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white leading-tight">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        
        <div class="bg-slate-800 border border-slate-700 p-4 sm:p-8 rounded-2xl shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-slate-800 border border-slate-700 p-4 sm:p-8 rounded-2xl shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

    </div>
</x-app-layout>
