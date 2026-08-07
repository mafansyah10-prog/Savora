<x-guest-layout>
    <div class="mb-6 text-xs text-gray-400 leading-relaxed uppercase tracking-wider font-semibold">
        Atur Ulang Kata Sandi
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="block mt-1 w-full text-xs" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" value="Kata Sandi Baru" />
            <div class="relative mt-1">
                <input
                    id="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="bg-[#0f1115] border-gray-800 text-gray-300 focus:border-[#e2c86e] focus:ring-[#e2c86e] rounded-xl shadow-sm transition duration-300 block w-full pl-4 pr-11 py-2.5"
                />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-600 hover:text-[#e2c86e] transition-colors duration-200 focus:outline-none"
                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                    <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    <span x-show="show" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <div class="relative mt-1">
                <input
                    id="password_confirmation"
                    :type="show ? 'text' : 'password'"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="bg-[#0f1115] border-gray-800 text-gray-300 focus:border-[#e2c86e] focus:ring-[#e2c86e] rounded-xl shadow-sm transition duration-300 block w-full pl-4 pr-11 py-2.5"
                />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-600 hover:text-[#e2c86e] transition-colors duration-200 focus:outline-none"
                    :aria-label="show ? 'Sembunyikan konfirmasi' : 'Tampilkan konfirmasi'">
                    <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    <span x-show="show" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center">
                Atur Ulang Kata Sandi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
