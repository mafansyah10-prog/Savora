<section>
    <header>
        <h2 class="text-xl font-bold text-white font-serif">
            {{ __('Perbarui Kata Sandi') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk menjaga keamanan.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div x-data="{ show: false }">
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" />
            <div class="relative mt-1">
                <x-text-input id="update_password_current_password" name="current_password" :type="'password'" x-bind:type="show ? 'text' : 'password'" class="block w-full pe-10" autocomplete="current-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gold-500 focus:outline-none transition-colors">
                    <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                    <i data-lucide="eye-off" x-show="show" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password" name="password" :type="'password'" x-bind:type="show ? 'text' : 'password'" class="block w-full pe-10" autocomplete="new-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gold-500 focus:outline-none transition-colors">
                    <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                    <i data-lucide="eye-off" x-show="show" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" :type="'password'" x-bind:type="show ? 'text' : 'password'" class="block w-full pe-10" autocomplete="new-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gold-500 focus:outline-none transition-colors">
                    <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                    <i data-lucide="eye-off" x-show="show" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-brand-cyan"
                >{{ __('Berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
